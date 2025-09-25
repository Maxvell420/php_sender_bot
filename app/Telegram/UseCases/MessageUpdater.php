<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MessageUpdate;
use App\Telegram\Enums;
use App\Models\ {
    Log,
    User,
    Post,
    State
};
use App\Repositories\StateRepository;
use App\Repositories\UserRepository;
use App\Telegram\Enums\States;
use App\Telegram\TelegramRequestFacade;
use App\Telegram\Values\CallbackDataValues;
use Illuminate\Database\Eloquent\Collection;

class MessageUpdater {

    public function __construct(
        private TelegramRequestFacade $telegramRequest,
        private InlineBuilder $inlineBuilder,
        private MessageBuilder $messageBuilder,
        private StateUpdater $stateUpdater,
        private UserRepository $userRepository,
        private StateRepository $stateRepository
    ) {}

    public function handleUpdate(MessageUpdate $update): void {
        $user_id = $update->findMessageFromId();

        if( !$user_id ) {
            // Если пришло не от бота то как-то обработать
            return;
        }

        $state = $this->stateRepository->findByUser($user_id);

        if( $update->hasBotCommands() ) {
            $this->handleBotCommand($update, $state);
            return;
        }

        // Обработка стейта
        if( $state ) {
            $handled = $this->stateUpdater->handleUpdate($update, $state);

            if( $handled ) {
                return;
            }
        }
        else {
            $this->sendDummyMessage($user_id);
        }
    }

    private function sendDummyMessage(int $user_id): void {
        $message = $this->messageBuilder->buildMessage(
            chat_id: $user_id,
            text: 'Я всего лишь бот :) Если возникли какие-то вопросы, то переходи в канал. Чтобы получить гайд, просто нажми /start. Все важные анонсы я пришлю сам!'
        );
        $this->telegramRequest->sendMessage($message);
    }

    private function handleBotCommand(MessageUpdate $data, ?State $state) {
        $user_id = $data->findMessageFromId();
        $user = $this->userRepository->findByTgId($user_id);

        if( !$user ) {
            $user = $this->createNewUser($user_id, true, $data->getUserName());
            // Вообще такого быть не должно
        }

        // если это команда то в ней есть текст
        $text = $data->findText();

        foreach(Enums\Commands::cases() as $case) {
            if( str_contains($text, $case->value) ) {
                $this->handleCommand($case, $data, $user, $state);
            }
        }

        return $data;
    }

    private function handleCommand(Enums\Commands $case, MessageUpdate $data, User $user, ?State $state): void {
        match ($case) {
            Enums\Commands::Start => $this->handleStart($data, $user, $state),
            Enums\Commands::Logs  => $this->handleLogs($data, $user),
            default => ''
        };
    }

    private function handleLogs(MessageUpdate $data, User $user): void {
        if( !$user->isAdmin() ) {
            $this->sendDummyMessage($user->tg_id);
            return;
        }

        $logs = new Log();
        /** @var Collection $logs */
        $logs = $logs->listLast();

        if( $logs->isEmpty() ) {
            $text = 'На данный момент логов нет';
            $message = $this->messageBuilder->buildMessage($user->tg_id, $text);
            $this->telegramRequest->sendMessage($message);
            return;
        }
        else {
            $text = '';

            $length = 0;

            /** @var Log $log */

            foreach($logs as $log) {
                $length = mb_strlen($text);

                if( $length > 3500 ) {
                    break;
                }

                $info = $log->info;

                $info = json_decode($info, true);

                if( !isset($info['message']) ) {
                    continue;
                }

                $text .= $log->id . ":" . $info['message'] . ' ' . $log->created_at . "\n";
            }
        }

        $message = $this->messageBuilder->buildMessage($user->tg_id, $text);
        $this->telegramRequest->sendMessage($message);
    }

    public function handleStart(MessageUpdate $data, User $user, ?State $state): void {
        if( !$user->isMember() ) {
            // Не отсылаю клавиатуру если юзера забанило
            return;
        }

        // Как-то надо подругому получить эти захардкоженные штуки
        $link = env('TG_CHANNEL_INVITE_LINK');
        $message = new Post()->getStartText();
        $file_id = env('TG_FILE_ID');
        $user_id = $data->findMessageFromId();

        $buttons = [];
        $buttons[] = $this->inlineBuilder->buildUrlButton('Вступить в канал', $link);

        if( $user->isAdmin() ) {
            if( $state && $state->state_id == States::Create_post->value ) {
                $keyboard = $this->inlineBuilder->buildCreatePostKeyboard();
            }
            else {
                $callbackData = new CallbackDataValues(Enums\Callback::CreatePost, 'yes');
                $buttons[] = $this->inlineBuilder->buildDataButton('Создать пост', json_encode($callbackData));

                $keyboard = $this->inlineBuilder->buildKeyboard($buttons);
            }
        }
        else {
            $keyboard = $this->inlineBuilder->buildKeyboard($buttons);
        }

        $message = $this->messageBuilder->buildDocument(chat_id:$user_id, caption:$message, file_id:$file_id, keyboard:$keyboard, params:['parse_mode' => 'MarkdownV2']);

        $this->telegramRequest->sendDocument($message);
    }

    private function createNewUser(int $tg_id, bool $member, ?string $user_name = null): User {
        $user = new User();
        $user->user_name = $user_name;
        $user->tg_id = $tg_id;

        $admin = env('TG_USER');

        if( $admin == $user->tg_id ) {
            $user->is_admin = 'yes';
        }

        $member == true ? $user->setMember() : $user->setKicked();
        $this->userRepository->persist($user);
        return $user;
    }
}
