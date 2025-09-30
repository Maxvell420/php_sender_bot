<?php

namespace App\Telegram\ErrorHandlers;

use App\Repositories\UpdateRepository;
use App\Telegram\Updates\Update;
use App\Telegram\ {
    Enums,
    TelegramRequestFacade
};
use App\Telegram\Updates\ {
    CallbackQueryUpdate,
    ChannelPostUpdate,
    MessageUpdate,
    MyChatMemberUpdate
};
use App\Telegram\UseCases\MessageBuilder;
use Error;

class TelegramBase {

    public function __construct(
        private UpdateRepository $updateRepository,
        private TelegramRequestFacade $telegramRequest,
        private MessageBuilder $messageBuilder
    ) {}

    public function handleErrorUpdate(Update $update, string $message): void {
        $update = match ($update->getType()) {
            Enums\UpdateType::MyChatMember => $this->handleMyChatMember($update, $message),
            Enums\UpdateType::Message => $this->handleMessage($update, $message),
            Enums\UpdateType::CallbackQuery => $this->handleCallbackQuery($update, $message),
            Enums\UpdateType::ChannelPost => $this->handleChannelPost($update, $message),
            default => throw new Error('WRONG_UPDATE')
        };
    }

    private function handleMyChatMember(MyChatMemberUpdate $update, string $message): void {
        $user_id = $update->getUserId();
        $message = $this->messageBuilder->buildMessage($user_id, $message);
        $this->telegramRequest->sendMessage($message);
    }

    private function handleMessage(MessageUpdate $update, string $message): void {
        $user_id = $update->getUserId();
        $message = $this->messageBuilder->buildMessage($user_id, $message);
        $this->telegramRequest->sendMessage($message);
    }

    private function handleCallbackQuery(CallbackQueryUpdate $update, string $message): void {
        $user_id = $update->getUserId();
        $message = $this->messageBuilder->buildMessage($user_id, $message);
        $this->telegramRequest->sendMessage($message);
    }

    private function handleChannelPost(ChannelPostUpdate $update, string $message): void {
        // пока таких ошибок не существует
        // $message = $this->messageBuilder->buildMessage($user_id, $message);
        // $this->telegramRequest->sendMessage($message);
    }
}
