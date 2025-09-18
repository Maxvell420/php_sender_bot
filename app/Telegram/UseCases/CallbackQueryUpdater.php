<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Models\State;
use App\Models\ {
    User,
    Job,
    JobUser
};
use App\Telegram\Enums;
use App\Telegram\TelegramRequestFacade;
use App\Telegram\Updates\CallbackQueryUpdate;

class CallbackQueryUpdater {

    public function __construct(
        private TelegramRequestFacade $telegramRequest,
        private InlineBuilder $inlineBuilder,
        private MessageBuilder $messageBuilder,
    ) {}

    public function handleUpdate($update): void {
        $data = $update->getData();
        match ($data->callback) {
            Enums\Callback::SendPost => $this->handleSendPost($update, $data->data),
            Enums\Callback::CreatePost => $this->handleCreatePost($update, $data->data),
            Enums\Callback::CheckPost => $this->handleCheckPost($update, $data->data)
        };
    }

    private function handleCheckPost(CallbackQueryUpdate $update, string $callback): void {
        $user_id = $update->getUserId();
        $state = new State();
        $existed_state = $state->findByUser($user_id);
        $message_id = $update->getMessageId();

        if( !$existed_state ) {
            $hideKeyboardMessage = $this->messageBuilder->buildHideInlineKeyboard($message_id, $user_id, $this->inlineBuilder->buildKeyboard([]));
            $this->telegramRequest->sendEditMessageReplyMarkup($hideKeyboardMessage);
            return;
        }

        $json = json_decode($existed_state->json, true);

        if( !$json ) {
            $message = $this->messageBuilder->buildMessage($user_id, 'Еще нет никакого сообщения для проверки');
            $this->telegramRequest->sendMessage($message);
            return;
        }

        match($json['method']) {
            TelegramActions::sendMessage->value => $this->telegramRequest->sendMessage($json['data']),
            TelegramActions::sendDocument->value => $this->telegramRequest->sendDocument($json['data']),
            TelegramActions::sendPhoto->value => $this->telegramRequest->sendPhoto($json['data']),
            TelegramActions::copyMessage->value => $this->telegramRequest->copyMessage($json['data']),
            TelegramActions::sendVideo->value => $this->telegramRequest->sendVideo($json['data']),
            TelegramActions::sendAnimation->value => $this->telegramRequest->sendAnimation($json['data']),
            TelegramActions::copyMessages->value => $this->telegramRequest->copyMessages($json['data'])
        };
    }

    private function handleCreatePost(CallbackQueryUpdate $update, string $callback): void {
        $user_id = $update->getUserId();
        $state = new State();
        $existed_state = $state->findByUser($user_id);
        $message_id = $update->getMessageId();

        if( $existed_state ) {
            $message = $this->messageBuilder->buildMessage($user_id, 'Уже жду пост для отправки');
            $this->telegramRequest->sendMessage($message);
            // Обновить тут клавиатуру
        }
        else {
            $message = $this->messageBuilder->buildMessage($user_id, 'Жду пост для отправки');
            $state->actor_id = $user_id;
            $state->state_id = Enums\States::Create_post->value;
            $state->save();
            $keyboard = $this->inlineBuilder->buildCreatePostKeyboard();
            $message = $this->messageBuilder->buildHideInlineKeyboard($message_id, $user_id, $keyboard);
            $this->telegramRequest->sendEditMessageReplyMarkup($message);
        }
    }

    private function handleSendPost(CallbackQueryUpdate $update, string $callback): void {
        $message_id = $update->getMessageId();
        $user_id = $update->getUserId();

        $state = new State()->findByUser($user_id);

        if( !$state ) {
            $hideKeyboardMessage = $this->messageBuilder->buildHideInlineKeyboard($message_id, $user_id, $this->inlineBuilder->buildKeyboard([]));
            $this->telegramRequest->sendEditMessageReplyMarkup($hideKeyboardMessage);
            return;
        }

        if( $callback != 'yes' ) {
            $state->delete();
            $hideKeyboardMessage = $this->messageBuilder->buildHideInlineKeyboard($message_id, $user_id, $this->inlineBuilder->buildKeyboard([]));
            $this->telegramRequest->sendEditMessageReplyMarkup($hideKeyboardMessage);
            return;
        }

        $json = json_decode($state->json, true);

        if( $json ) {
            $message = $this->messageBuilder->buildMessage($user_id, 'Нету сообщения для отправки');
            $this->telegramRequest->sendMessage($message);
            return;
        }

        [$message, $action] = match ($json['method']) {
            TelegramActions::copyMessage->value => [
                $this->messageBuilder->buildCopyMessage($user_id, $user_id, $json['data']['message_id']),
                TelegramActions::copyMessage
            ],
            TelegramActions::copyMessages->value => [
                $this->messageBuilder->buildCopyMessages($user_id, $user_id, $json['data']['message_ids']),
                TelegramActions::copyMessages
            ],
            default => [
                $json['data'],
                TelegramActions::tryFrom($json['method'])
            ]
        };

        $job = new Job();
        $job->json = json_encode(['message' => $message, 'action' => $action]);

        $job->actor_id = $user_id;
        $job->job_type = Enums\JobTypes::Create_post->value;
        $job->save();

        $user = new User;
        $users = $user->listActiveUsers();
        $count = 0;

        foreach($users as $user) {
            if( $user_id == $user->tg_id ) {
                continue;
            }

            $count++;

            $userJob = new JobUser();
            $userJob->actor_id = $user->tg_id;
            $userJob->job_id = $job->id;

            $userJob->save();
        }

        $message = $this->messageBuilder->buildMessage($user_id, "Пост в скором времени будет разослан $count пользователям");
        $this->telegramRequest->sendMessage($message);
    }
}
