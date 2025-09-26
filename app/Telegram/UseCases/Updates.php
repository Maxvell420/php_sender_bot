<?php

namespace App\Telegram\UseCases;

use App\Telegram\ {
    Enums,
    TelegramUpdatesFacade
};
use App\Models\ {
    Job,
    Log,
    Update
};
use App\Telegram\Updates\ {
    CallbackQueryUpdate,
    ChannelPostUpdate,
    MyChatMemberUpdate,
    MessageUpdate
};
use App\Telegram\Updates\Update as UpdateInterface;
use Throwable;
use App\Http\Exceptions\ValidationException;
use App\Libs\Telegram\TelegramRequest;
use App\Telegram\Exceptions\TelegramBaseException;
use Error;

// Корневой класс который все разруливает
class Updates {

    public function __construct(private TelegramRequest $telegramRequest) {}

    public function work(): void {}

    public function handleUpdate(array $data): void {
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

        // Может быть стоит завести дефолтный обьект на такие ситуации чтобы вытаскивать $update_id
        $update_id = $data['update_id'];
        $this->saveUpdate($update_id);
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
            if( $update->getUserId() ) {
                $facade->sendErrorMessage($update->getUserId(), $e->getMessage());
            }

            $log = new Log();
            $log->info = json_encode(['message' => $e->getMessage()]);
            $log->save();
        } catch (Error $e) {
            $facade->sendErrorMessage(env('TG_USER'), 'Что-то пошло нет так');
            $log = new Log();
            $log->info = json_encode(['message' => $e->getMessage()]);
            $log->save();
        }
    }

    private function saveUpdate(int $update_id) {
        $update = new Update;
        $update->update_id = $update_id;
        $update->save();
    }

    private function buildVO(string $class, array $data): UpdateInterface {
        try {
            return $class::from($data);
        } catch (Throwable) {
            throw new ValidationException('Не смогли сконструировать обьект из сообщения: ' . json_encode($data));
        }
    }

    public function handleJob(Job $job): void {
        $facade = new TelegramUpdatesFacade($this->telegramRequest);
        $facade->handleJob($job);
    }

    public function getUpdates(int $update_id, int $timeout): array {
        $facade = new TelegramUpdatesFacade($this->telegramRequest);
        return $facade->getUpdates($update_id, $timeout);
    }
}
