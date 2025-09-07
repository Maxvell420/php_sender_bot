<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramRequest;
use App\Telegram\Updates\MessageUpdate;
use App\Telegram\Updates\Update;
use App\Telegram\Enums;
use App\Models\{
    User,
    Post
};
use App\Telegram\Values\CallbackDataValues;

class MessageUpdater extends UpdateHandler
{

    public function __construct(
        private TelegramRequest $telegramRequest,
        private InlineBuilder $inlineBuilder = new InlineBuilder,
        private MessageBuilder $messageBuilder = new MessageBuilder,
    ) {}

    public function handleUpdate(Update $values): void
    {
        /**
         * @var MessageUpdate $values
         */

        $user_id = $values->findMessageFromId();

        if (!$user_id) {
            // Если пришло не от бота то как-то обработать
            return;
        }

        if ($values->hasBotCommands()) {
            $this->handleBotCommand($values);
        } else {
            $builder = new MessageBuilder;
            $message = $builder->buildMessage(
                $user_id,
                "Я всего лишь бот :) Если возникли какие-то вопросы, то переходи в канал. Чтобы получить гайд, просто нажми /start. Все важные анонсы я пришлю сам!"
            );
            $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $message);
        }
    }

    private function handleBotCommand(MessageUpdate $data)
    {
        $user_id = $data->findMessageFromId();
        $user = new User()->findByTgId($user_id);

        if (!$user) {
            $user = $this->createNewUser($user_id, true, $data->getUserName());
            // Вообще такого быть не должно
        }

        // если это команда то в ней есть текст
        $text = $data->findText();

        foreach (Enums\Commands::cases() as $case) {
            if (str_contains($text, $case->value)) {
                $this->handleCommand($case, $data, $user);
            }
        }

        return $data;
    }

    private function handleCommand(Enums\Commands $case, MessageUpdate $data, User $user): void
    {
        match ($case) {
            Enums\Commands::Start => $this->handleStart($data, $user),
            default => ''
        };
    }

    public function handleStart(MessageUpdate $data, User $user): void
    {
        if (!$user->isMember()) {
            // Не отсылаю клавиатуру если юзера забанило
            return;
        }
        $link = env('TG_CHANNEL_INVITE_LINK');
        $message = new Post()->getStartText();
        $file_id = env('TG_FILE_ID');
        $user_id = $data->findMessageFromId();

        $buttons = [];
        $buttons[] = $this->inlineBuilder->buildUrlButton('Вступить в канал', $link);

        if ($user->isAdmin()) {
            $callbackData = new CallbackDataValues(Enums\Callback::CreatePost, 'yes');
            $buttons[] = $this->inlineBuilder->buildDataButton('Создать пост', json_encode($callbackData));
        }

        $keyboard = $this->inlineBuilder->buildKeyboard($buttons);

        $message = $this->messageBuilder->buildDocument($user_id, caption: $message, file_id: $file_id, keyboard: $keyboard);

        $this->telegramRequest->sendMessage(TelegramActions::sendDocument, $message);
    }

    private function createNewUser(int $tg_id, bool $member, ?string $user_name = null): User
    {
        $user = new User();
        $user->user_name = $user_name;
        $user->tg_id = $tg_id;

        $admin = env('TG_USER');
        if ($admin == $user->tg_id) {
            $user->is_admin = 'yes';
        }


        $member == true ? $user->setMember() : $user->setKicked();
        $user->save();
        return $user;
    }
}
