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
use App\Telegram\InlineKeyboard\InlineKeyboard;
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
            Enums\Callback::CreatePost => $this->handleCreatePost($update, $data->data)
        };
    }

    // пока что всегда работает без callBack
    private function handleCreatePost(CallbackQueryUpdate $update, string $callback): void {
        $user_id = $update->getUserId();
        $state = new State();
        $existed_state = $state->findByUser($user_id);

        if( $existed_state ) {
            $message = $this->messageBuilder->buildMessage($user_id, 'Уже жду пост для отправки');
        }
        else {
            $message = $this->messageBuilder->buildMessage($user_id, 'Жду пост для отправки');
            $state->actor_id = $user_id;
            $state->state_id = Enums\States::Create_post->value;
            $state->save();
        }

        $this->telegramRequest->sendMessage($message);
    }

    private function handleSendPost(CallbackQueryUpdate $update, string $callback): void {
        $message_id = $update->getMessageId();
        $user_id = $update->getUserId();
        $hideKeyboardMessage = $this->messageBuilder->buildHileInlineKeyboard($message_id, $user_id, $this->inlineBuilder->buildKeyboard([]));
        $this->telegramRequest->sendEditMessageReplyMarkup($hideKeyboardMessage);

        if( $callback != 'yes' ) {
            return;
        }

        $message_id = $update->getMessageId();
        $message = $this->messageBuilder->buildCopyMessage($user_id, $user_id, $message_id);

        $job = new Job();
        $job->json = json_encode(['message' => $message, 'action' => TelegramActions::copyMessage->value]);

        $job->actor_id = $user_id;
        $job->job_type = Enums\JobTypes::Create_post->value;
        $job->save();

        $user = new User;
        $users = $user->listActiveUsers();
        $count = 0;

        foreach($users as $user) {
            // if ($user_id == $user->tg_id) {
            //     continue;
            // }

            $count++;

            $userJob = new JobUser();
            $userJob->actor_id = $user->tg_id;
            $userJob->job_id = $job->id;

            $userJob->save();
        }

        $message = $this->messageBuilder->buildMessage($user_id, "Пост в скором времени будет разослан $count пользователям");
        $this->telegramRequest->sendMessage($message);
    }

    private function buildPostMessage(
        TelegramActions $type,
        CallbackQueryUpdate $update,
        string $message,
        int $user_id,
        array $params = [],
        ?InlineKeyboard $keyboard = null
    ): array {
        switch($type) {
            case TelegramActions::sendPhoto:
                $photo = $update->getPhoto();
                $file = array_pop($photo);
                $message = $this->messageBuilder->buildPhoto(chat_id:$user_id, caption:$message, file_id:$file['file_id'], keyboard:$keyboard, params:$params);
                break;

            case TelegramActions::sendDocument:
                $document = $update->getDocument();
                $message = $this->messageBuilder->buildDocument(chat_id:$user_id, caption:$message, file_id:$document->file_id, keyboard:$keyboard, params:$params);
                break;

            default:
                $message = $this->messageBuilder->buildMessage(chat_id:$user_id, text:$message, keyboard:$keyboard, params:$params);
                break;
        };
        return $message;
    }
}
