<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramRequest;
use App\Models\State;
use App\Models\{
    User,
    Job,
    JobUser
};
use App\Telegram\Enums;
use App\Telegram\Updates\CallbackQueryUpdate;
use App\Telegram\Updates\Update as UpdateInterface;

class CallbackQueryUpdater extends UpdateHandler
{

    public function __construct(
        private TelegramRequest $telegramRequest,
        private InlineBuilder $inlineBuilder = new InlineBuilder,
        private MessageBuilder $messageBuilder = new MessageBuilder,
    ) {}

    public function handleUpdate(UpdateInterface $update): void
    {
        /**
         * @var CallbackQueryUpdate $update
         */

        $data = $update->getData();

        match ($data->callback) {
            Enums\Callback::SendPost => $this->handleSendPost($update, $data->data),
            Enums\Callback::CreatePost => $this->handleCreatePost($update, $data->data)
        };
    }

    // пока что всегда работает без callBack
    private function handleCreatePost(CallbackQueryUpdate $update, string $callback): void
    {
        $user_id = $update->getUserId();
        $state = new State();
        $existed_state = $state->findByUser($user_id);

        if ($existed_state) {
            $message = $this->messageBuilder->buildMessage($user_id, 'Уже жду пост для отправки');
        } else {
            $message = $this->messageBuilder->buildMessage($user_id, 'Жду пост для отправки');
            $state->actor_id = $user_id;
            $state->state_id = Enums\States::Create_post->value;
            $state->save();
        }

        $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $message);
    }

    private function handleSendPost(CallbackQueryUpdate $update, string $callback): void
    {
        $message_id = $update->getMessageId();

        $user_id = $update->getUserId();

        $hideKeyboardMessage = $this->messageBuilder->buildHileInlineKeyboard($message_id, $user_id, $this->inlineBuilder->buildKeyboard([]));
        $this->telegramRequest->sendMessage(TelegramActions::editMessageReplyMarkup, $hideKeyboardMessage);

        if ($callback != 'yes') {
            return;
        }

        $user = new User;

        // Как-то подправить эту штуку чтобы не изменять message
        if ($update->hasDocument()) {
            $document = $update->getDocument();
            $action = TelegramActions::sendDocument;
            $message = $this->messageBuilder->buildDocument($user_id, $update->getCaption(), $document->file_id);
        } elseif ($update->hasPhoto()) {
            $photo = $update->getPhoto();
            $file = array_pop($photo);
            $action = TelegramActions::sendPhoto;
            $message = $this->messageBuilder->buildPhoto($user_id, $update->getCaption(), $file['file_id']);
        } elseif ($update->hasText()) {
            $text = $update->getText();
            $action = TelegramActions::sendMessage;
            $message = $this->messageBuilder->buildMessage($user_id, $text);
        }

        if (!isset($message)) {
            return;
        }

        $job = new Job();
        $job->json = json_encode(['message' => $message, 'action' => $action->value]);
        $job->actor_id = $user_id;
        $job->job_type = Enums\JobTypes::Create_post->value;
        $job->save();

        $users = $user->listActiveUsers();
        $count = 0;

        foreach ($users as $user) {
            if ($user_id == $user->tg_id) {
                continue;
            }

            $message['chat_id'] = $user->tg_id;
            $count++;

            $userJob = new JobUser();
            $userJob->actor_id = $user->tg_id;
            $userJob->job_id = $job->id;

            $userJob->save();
        }

        $message = $this->messageBuilder->buildMessage($user_id, "Пост в скором времени будет разослан $count пользователям");
        $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $message);
    }
}
