<?php

namespace App\Telegram\ErrorHandlers;

use App\Libs\Telegram\TelegramActions;
use App\Models\ {
    Log,
};
use App\Repositories\ {
    LogRepository,
    UserRepository
};

use App\Telegram\ {
    Enums,
};
use App\Telegram\Updates\ {
    Update,
    CallbackQueryUpdate,
    MyChatMemberUpdate
};

class TelegramMessage {

    public function __construct(private UserRepository $userRepository, private LogRepository $logRepository) {}

    private const string WRONG_REPLY_MARKUP = "Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message";

    public function handleUpdateError(Update $update, int $status, string $error_message): void {
        match ($update->getType()) {
            Enums\UpdateType::MyChatMember => $this->handleNewChatMember($update, $status, $error_message),
            Enums\UpdateType::CallbackQuery => $this->handleCallbackQuery($update, $status, $error_message),
            default => $this->handleSendData($update, $error_message)
        };
    }

    private function handleNewChatMember(MyChatMemberUpdate $update, int $status, string $error_message): void {
        if( $status == 403 ) {
            $user_id = $update->getUserId();
            $user = $this->userRepository->findByTgId($user_id);

            if( $user ) {
                $user->setKicked();
                $this->userRepository->persist($user);
            }

            return;
        }
    }

    private function handleCallbackQuery(CallbackQueryUpdate $update, int $status, string $error_message): void {
        if( $error_message == self::WRONG_REPLY_MARKUP && $status == 400 ) {
            return;
        }

        $this->handleSendData($update, $status, $error_message);
    }

    public function handleTelegramRequest(Update $data, int $status, string $error_message): void {
        if( $status == 403 && isset($data['chat_id']) ) {
            $user = $this->userRepository->findByTgId($data['chat_id']);

            if( $user ) {
                $user->setKicked();
                $user->save();
            }

            return;
        }

        $this->handleSendData($data, $error_message);
    }

    private function handleSendData(Update $update, string $error_message): void {
        $save_data = ['message' => $error_message, 'data' => $update];
        $data = json_encode($save_data);
        $this->logError($data);
    }

    private function logError(string $message): void {
        $log = new Log();
        $log->info = $message;
        $this->logRepository->persist($log);
    }
}
