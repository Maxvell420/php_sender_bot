<?php

namespace App\Telegram\UseCases;

use App\Telegram\ {
    Enums
};
use App\Models\ {
    Update,
    State
};
use App\Telegram\Updates\ {
    CallbackQueryUpdate,
    MyChatMemberUpdate,
    MessageUpdate
};
use App\Telegram\Updates\Update as UpdateInterface;
use Throwable;
use App\Http\Exceptions\ValidationException;
use App\Libs\Telegram\TelegramRequest;
use Exception;

// Корневой класс который все разруливает
class Updates {

    public function __construct(private TelegramRequest $telegramRequest) {}

    public function handleUpdate(array $update): void {
        foreach(Enums\UpdateType::cases() as $case) {
            if( isset($update[$case->value]) ) {
                $this->processUpdate($case, $update);
            }
        }
    }

    // Позволяет не упасть и идти дальше
    public function handleErrorUpdate(array $update): void {
        $errorUpdate_id = $this->getUpdateID($update);
        $this->saveUpdate($errorUpdate_id);
    }

    private function getUpdateID(array $update): int {
        foreach(Enums\UpdateType::cases() as $case) {
            if( isset($update[$case->value]) ) {
                $update = match ($case) {
                    Enums\UpdateType::MyChatMember => $this->buildVO(MyChatMemberUpdate::class, $update),
                    Enums\UpdateType::Message => $this->buildVO(MessageUpdate::class, $update),
                    Enums\UpdateType::CallbackQuery => $this->buildVO(CallbackQueryUpdate::class, $update)
                };
                return $update->getUpdateId();
            }
        }

        // что-то другое надо делать
        return $update['update_id'];
    }

    private function processUpdate(Enums\UpdateType $case, array $update): void {
        [$updater, $values] = match ($case) {
            Enums\UpdateType::MyChatMember => [new MyChatMemberUpdater($this->telegramRequest), $this->buildVO(MyChatMemberUpdate::class, $update)],
            Enums\UpdateType::Message => [new MessageUpdater($this->telegramRequest), $this->buildVO(MessageUpdate::class, $update)],
            Enums\UpdateType::CallbackQuery => [new CallbackQueryUpdater($this->telegramRequest), $this->buildVO(CallbackQueryUpdate::class, $update)]
        };

        $need_handle = true;

        if( $values->hasFrom() ) {
            // если нашли юзера и у него есть state то первое действие это всегда state
            $values->getUserId();
            $state = new State()->findByUser($values->getUserId());

            // Стейты только для сообщений?
            if( $values instanceof MessageUpdate ) {
                if( $state ) {
                    $state_updater = new StateUpdater($this->telegramRequest);
                    $need_handle = $state_updater->handleUpdate($values, $state);
                }
            }
        }

        if( $need_handle ) {
            $updater->handleUpdate($values);
        }

        $this->saveUpdate($values->getUpdateId());
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
            throw new ValidationException('WRONG_DATA', 422);
        }
    }
}
