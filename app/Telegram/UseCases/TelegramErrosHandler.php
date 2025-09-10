<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Models\ {
    Log,
    User
};

// Обработка различных ошибок
class TelegramErrosHandler {

    private const string WRONG_REPLY_MARKUP = "Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message";

    public function handleTelegramRequest(TelegramActions $action, array $data, int $status, string $error_message): void {
        if( $status == 403 && isset($data['chat_id']) ) {
            $user = new User()->findByTgId($data['chat_id']);

            if( $user ) {
                $user->setKicked();
                $user->save();
            }

            return;
        }

        match($action) {
            TelegramActions::editMessageReplyMarkup => $this->handleSendReplyMarkUp($data, $status, $error_message),
            default => $this->handleSendData($data, $status, $error_message)
        };
    }

    private function handleSendReplyMarkUp(array $data, int $status, string $error_message): void {
        if( $error_message == self::WRONG_REPLY_MARKUP  && $status == 400 ) {
            return;
        }

        $this->handleSendData($data, $status, $error_message);
    }

    private function handleSendData(array $data, int $status, string $error_message): void {
        $save_data = ['message' => $data, 'message' => $error_message];
        $data = json_encode($save_data);
        $this->logError($data);
    }

    private function logError(string $message): void {
        $log = new Log();
        $log->info = $message;
        $log->save();
    }

    public function handleWrongUpdate(array $data): void {}

    public function handleCodeError(string $message): void {}

    public function handleException(string $message): void {}
}
