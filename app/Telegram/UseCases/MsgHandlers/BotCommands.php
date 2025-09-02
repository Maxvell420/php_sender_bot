<?php

namespace App\Telegram\UseCases\MsgHandlers;

use App\Libs\Telegram\TelegramRequest;
use App\Telegram\Updates\MessageUpdate;
use App\Models\{
    User,
    Post
};
use App\Telegram\Updates\Update;
use App\Telegram\Enums;
use App\Telegram\UseCases\InlineBuilder;
use App\Telegram\UseCases\MessageBuilder;

class BotCommands
{

    private TelegramRequest $telegramRequest;

    public function __construct(
        private InlineBuilder $inlineBuilder = new InlineBuilder,
        private MessageBuilder $messageBuilder = new MessageBuilder,
    ) {
        $this->telegramRequest = new TelegramRequest(env('TG_BOT_SECRET'));
    }

    public function handleUpdate(MessageUpdate $data): Update
    {
        $user_id = $data->findMessageFromId();
        $user = new User()->findByTgId($user_id);

        if (!$user) {
            $user = $this->createNewUser($data->getUserName(), $user_id, true);
            // Вообще такого быть не должно
        }

        // если это команда то в ней есть текст
        $text = $data->findText();

        foreach (Enums\Commands::cases() as $case) {
            if (str_contains($text, $case->value)) {
                $this->handleCommand($case, $data);
            }
        }

        return $data;
    }

    private function handleCommand(Enums\Commands $case, MessageUpdate $data): void
    {
        match ($case) {
            Enums\Commands::Start => $this->handleStart($data),
            default => ''
        };
    }

    private function handleStart(MessageUpdate $data): void
    {
        $link = env('TG_CHANNEL_INVITE_LINK');
        $message = new Post()->getStartText();
        $file_id = env('TG_FILE_ID');
        $user_id = $data->findMessageFromId();

        $buttons = [];
        $buttons[] = $this->inlineBuilder->buildUrlButton('Вступить в канал', $link);
        $keyboard = $this->inlineBuilder->buildKeyboard($buttons);

        $message = $this->messageBuilder->buildDocument($user_id, caption: $message, file_id: $file_id, keyboard: $keyboard);


        $this->telegramRequest->sendDocument($message);
    }

    private function handleUndefined(MessageUpdate $data): void {}

    private function createNewUser(string $user_name, int $tg_id, bool $member): User
    {
        $user = new User();
        $user->user_name = $user_name;
        $user->tg_id = $tg_id;
        $member == true ? $user->setMember() : $user->setKicked();
        $user->save();
        return $user;
    }
}
