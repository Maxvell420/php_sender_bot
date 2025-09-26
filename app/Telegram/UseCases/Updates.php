<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramApiException;
use App\Telegram\ {
    Enums,
    TelegramUpdatesFacade
};
use App\Models\ {
    Job,
    Update
};
use App\Telegram\Updates\ {
    CallbackQueryUpdate,
    ChannelPostUpdate,
    MyChatMemberUpdate,
    MessageUpdate
};
use App\Telegram\Updates\Update as UpdateInterface;
use App\Libs\Telegram\TelegramRequest;
use App\Repositories\JobRepository;
use App\Repositories\UpdateRepository;
use App\Telegram\Exceptions\TelegramBaseException;
use Error;

// Корневой класс который все разруливает
class Updates {

    public function __construct(
        private TelegramRequest $telegramRequest,
        private UpdateRepository $updateRepository,
        private JobRepository $jobRepository
    ) {}

    public function work(): void {
        while( true ) {
            $update_id = $this->updateRepository->getNextUpdateId();
            $job = $this->jobRepository->findFirstNotCompleted();

            if( $job ) {
                $this->handleJob($job);
            }

            $updates = $this->getUpdates($update_id, 10);

            foreach($updates['result'] as $update) {
                $this->handleUpdate($update);
            }
        }
    }

    private function handleUpdate(array $data): void {
        foreach(Enums\UpdateType::cases() as $case) {
            if( isset($data[$case->value]) ) {
                $update = match ($case) {
                    Enums\UpdateType::MyChatMember => $this->buildVO(MyChatMemberUpdate::class, $data),
                    Enums\UpdateType::Message => $this->buildVO(MessageUpdate::class, $data),
                    Enums\UpdateType::CallbackQuery => $this->buildVO(CallbackQueryUpdate::class, $data),
                    Enums\UpdateType::ChannelPost => $this->buildVO(ChannelPostUpdate::class, $data)
                };

                $this->processUpdate($update);
            }
        }

        if( !isset($update) ) {
            throw new Error('WRONG_UPDATE');
        }

        $this->saveUpdate($update->getUpdateId());
    }

    private function processUpdate(UpdateInterface $update) {
        $facade = new TelegramUpdatesFacade($this->telegramRequest);

        try {
            match ($update->getType()) {
                Enums\UpdateType::MyChatMember => $facade->handleNewChatmember($update),
                Enums\UpdateType::Message => $facade->handleMessage($update),
                Enums\UpdateType::CallbackQuery => $facade->handleCallback($update),
                ENums\UpdateType::ChannelPost => $facade->handleChannelPost($update)
            };
        } catch (TelegramBaseException $e) {

        } catch (TelegramApiException $e) {

        }
    }

    private function saveUpdate(int $update_id) {
        $update = new Update;
        $update->update_id = $update_id;
        $this->updateRepository->persist($update);
    }

    private function buildVO(string $class, array $data): UpdateInterface {
        return $class::from($data);
    }

    private function handleJob(Job $job): void {
        $facade = new TelegramUpdatesFacade($this->telegramRequest);
        $facade->handleJob($job);
    }

    private function getUpdates(int $update_id, int $timeout): array {
        $facade = new TelegramUpdatesFacade($this->telegramRequest);
        return $facade->getUpdates($update_id, $timeout);
    }
}
