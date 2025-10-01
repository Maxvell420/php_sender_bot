<?php

namespace App\Telegram\ErrorHandlers;

use App\Models\{
    Log,
};
use App\Repositories\{
    LogRepository,
    UserRepository
};

use App\Telegram\{
    Enums,
};
use App\Telegram\Updates\{
    Update,
    CallbackQueryUpdate,
    MessageUpdate,
    MyChatMemberUpdate
};

class TelegramMessage
{

    public function __construct(private UserRepository $userRepository, private LogRepository $logRepository) {}

    private const string BLOCKED = 'Forbidden: bot was blocked by the user';
    private const string WRONG_REPLY_MARKUP = 'Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message';

    public function handleWrongUpdate(Update $update, string $error_message, int $status): void
    {
        match ($update->getType()) {
            Enums\UpdateType::MyChatMember => $this->handleNewChatMember($update, $status, $error_message),
            Enums\UpdateType::CallbackQuery => $this->handleCallbackQuery($update, $status, $error_message),
            Enums\UpdateType::Message => $this->handleMessage($update, $status, $error_message),
            default => $this->handleSendData($update, $error_message)
        };
    }

    public function handleWrongGetUpdates(string $message): void
    {
        $save_data = ['message' => $message, 'data' => 'no data'];
        $data = json_encode($save_data);
        $this->logError($data);
    }

    // Сейчас это просто блокировка юзера
    public function handleWrongJob(int $user_id, string $text): void
    {
        if ($text == self::BLOCKED) {
            $this->blockUser($user_id);
        }
    }

    private function handleMessage(MessageUpdate $update, int $status, string $error_message): void
    {
        if ($error_message == self::BLOCKED) {
            $user_id = $update->getUserId();
            $user = $this->userRepository->findByTgId($user_id);
            $user->setKicked();
            $this->userRepository->persist($user);
            return;
        }

        $this->handleSendData($update, $error_message);
    }

    private function blockUser(int $user_id): void
    {
        $user = $this->userRepository->findByTgId($user_id);

        if ($user) {
            $user->setKicked();
            $this->userRepository->persist($user);
        }
    }

    private function handleNewChatMember(MyChatMemberUpdate $update, int $status, string $error_message): void
    {
        if ($status == 403) {
            $user_id = $update->getUserId();
            $this->blockUser($user_id);
            return;
        }

        $this->handleSendData($update, $error_message);
    }

    private function handleCallbackQuery(CallbackQueryUpdate $update, int $status, string $error_message): void
    {
        if ($error_message == self::WRONG_REPLY_MARKUP && $status == 400) {
            return;
        }

        $this->handleSendData($update, $error_message);
    }

    private function handleSendData(Update $update, string $error_message): void
    {
        $save_data = ['message' => $error_message, 'data' => $update];
        $data = json_encode($save_data);
        $this->logError($data);
    }

    private function logError(string $message): void
    {
        $log = new Log();
        $log->info = $message;
        $this->logRepository->persist($log);
    }
}
