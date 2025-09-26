<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\ {
    TelegramApiException,
    TelegramRequest
};
use App\Telegram\ {
    Enums,
    TelegramRequestFacade,
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
use App\Repositories\ {
    JobRepository,
    UpdateRepository
};
use App\Telegram\Exceptions\TelegramBaseException;
use Error;

// Корневой класс который все разруливает
class Updates {

    public function __construct(
        private TelegramUpdatesFacade $telegramFacade,
        private TelegramRequestFacade $telegramRequestFacade
    ) {}

    public function work(): void {
        while( true ) {
            $job = $this->telegramFacade->findFirstJobNotCompleted();

            if( $job ) {
                $this->handleJob($job);
            }

            $update_id = $this->telegramFacade->getNextUpdateId();
            $updates = $this->getUpdates($update_id, 10);

            if( empty($update) || empty($update['result']) ) {
                continue;
            }

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
        try {
            match ($update->getType()) {
                Enums\UpdateType::MyChatMember => $this->telegramFacade->handleNewChatmember($update),
                Enums\UpdateType::Message => $this->telegramFacade->handleMessage($update),
                Enums\UpdateType::CallbackQuery => $this->telegramFacade->handleCallback($update),
                ENums\UpdateType::ChannelPost => $this->telegramFacade->handleChannelPost($update)
            };
        } catch (TelegramBaseException $e) {

        } catch (TelegramApiException $e) {

        }
    }

    private function saveUpdate(int $update_id) {
        $update = new Update;
        $update->update_id = $update_id;
        $this->telegramFacade->persistUpdate($update);
    }

    private function buildVO(string $class, array $data): UpdateInterface {
        return $class::from($data);
    }

    private function handleJob(Job $job): void {
        $this->telegramFacade->handleJob($job);
    }

    private function getUpdates(int $update_id, int $timeout): array {
        return $this->telegramRequestFacade->getUpdates($update_id, $timeout);
    }
}
