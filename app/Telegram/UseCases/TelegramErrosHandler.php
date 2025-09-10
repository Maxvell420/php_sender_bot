<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Models\User;

// Обработка различных ошибок
class TelegramErrosHandler {

    public function handleTelegramRequest(TelegramActions $action, array $data, int $status): void {
        if( $status == 403 && isset($data['chat_id']) ) {
            $user = new User()->findByTgId($data['chat_id']);

            if( $user ) {
                $user->setKicked();
                $user->save();
            }

            return;
        }

        match($action) {
            TelegramActions::editMessageReplyMarkup => $this->handleSendReplyMarkUp($data, $status),
            default => $this->handleSendData($data, $status)
        };
    }

    private function handleSendReplyMarkUp(array $data, int $status): void {}
    private function handleSendData(array $data, int $status): void {}

    private function logError(): void {}

    public function handleWrongUpdate(array $data): void {}

    public function handleCodeError(string $message): void {}

    public function handleException(string $message): void {}
}
