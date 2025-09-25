<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Models\ {
    Log,
};
use App\Repositories\ {
    LogRepository,
    UserRepository
};


// Обработка различных ошибок при отправки сообщений в телеграм
class TelegramWrongMessagesHandler {

    public function __construct(private UserRepository $userRepository, private LogRepository $logRepository) {}

    private const string WRONG_REPLY_MARKUP = "Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message";

    public function handleTelegramRequest(TelegramActions $action, array $data, int $status, string $error_message): void {
        if( $status == 403 && isset($data['chat_id']) ) {
            $user = $this->userRepository->findByTgId($data['chat_id']);

            if( $user ) {
                $user->setKicked();
                $user->save();
            }

            return;
        }

        match($action) {
            TelegramActions::editMessageReplyMarkup => $this->handleSendReplyMarkUp($data, $status, $error_message),
            default => $this->handleSendData($data, $error_message)
        };
    }

    private function handleSendReplyMarkUp(array $data, int $status, string $error_message): void {
        if( $error_message == self::WRONG_REPLY_MARKUP  && $status == 400 ) {
            return;
        }

        $this->handleSendData($data, $status, $error_message);
    }

    private function handleSendData(array $data, string $error_message): void {
        $save_data = ['message' => $error_message, 'data' => $data];
        $data = json_encode($save_data);
        $this->logError($data);
    }

    private function logError(string $message): void {
        $log = new Log();
        $log->info = $message;
        $this->logRepository->persist($log);
    }
}
