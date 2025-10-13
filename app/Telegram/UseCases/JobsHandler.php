<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramApiException;
use App\Models\{
    Job,
    JobUser
};
use App\Telegram\Enums;
use App\Telegram\ErrorHandlers\TelegramMessage;
use App\Telegram\TelegramRequestFacade;

class JobsHandler
{

    public function __construct(
        private TelegramRequestFacade $telegramRequest,
        private MessageBuilder $messageBuilder,
        private TelegramMessage $msgErrHandler
    ) {}

    public function handleJob(Job $job): void
    {
        match ($job->job_type) {
            Enums\JobTypes::Create_post->value => $this->handleSendPost($job),
        };
    }

    private function handleSendPost(Job $job): void
    {
        $count = 0;

        $update = json_decode($job->json, true);
        $action = $update['action'];
        $userJobs = new JobUser();
        $userJobs = $userJobs->listByJob($job->id);
        /**
         * @var JobUser[] $userJobs
         */
        $message = $update['message'];
        $messages_send = 0;

        foreach ($userJobs as $user) {
            if ($messages_send == 1) {
                sleep(1);
                return;
            }

            if ($user->isCompleted()) {
                continue;
            }

            $count++;
            $message['chat_id'] = $user->actor_id;

            try {
                match ($action) {
                    TelegramActions::copyMessage->value => $this->telegramRequest->copyMessage($message),
                    TelegramActions::copyMessages->value => $this->telegramRequest->copyMessages($message),
                    TelegramActions::sendMessage->value => $this->telegramRequest->sendMessage($message),
                    TelegramActions::sendDocument->value => $this->telegramRequest->sendDocument($message),
                    TelegramActions::sendPhoto->value => $this->telegramRequest->sendPhoto($message),
                    TelegramActions::sendVideo->value => $this->telegramRequest->sendVideo($message),
                    TelegramActions::sendAnimation->value => $this->telegramRequest->sendAnimation($message),
                };
            } catch (TelegramApiException $e) {
                $this->msgErrHandler->handleWrongJob($user->actor_id, $e->getMessage());
            }

            $user->complete();
            $user->save();
            $messages_send++;
        }

        $message = $this->messageBuilder->buildMessage(chat_id: $job->actor_id, text: "Пост был разослан $count пользователям");
        $this->telegramRequest->sendMessage($message);
        $job->complete();
        $job->save();
    }
}
