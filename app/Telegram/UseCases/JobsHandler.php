<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Models\{
    Job,
    JobUser
};
use App\Telegram\Enums;
use App\Telegram\TelegramRequestFacade;

class JobsHandler
{

    public function __construct(private TelegramRequestFacade $telegramRequest, private MessageBuilder $messageBuilder) {}

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

        $actor_id = $job->actor_id;

        foreach ($userJobs as $user) {
            if ($user->isCompleted()) {
                continue;
            }

            if ($user->actor_id == $actor_id) {
                continue;
            }

            $count++;
            $message['chat_id'] = $user->actor_id;
            match ($action) {
                TelegramActions::copyMessage->value => $this->telegramRequest->copyMessage($message),
                TelegramActions::copyMessages->value => $this->telegramRequest->copyMessages($message),
                TelegramActions::sendMessage->value => $this->telegramRequest->sendMessage($message),
                TelegramActions::sendDocument->value => $this->telegramRequest->sendDocument($message),
                TelegramActions::sendPhoto->value => $this->telegramRequest->sendPhoto($message),
                TelegramActions::sendVideo->value => $this->telegramRequest->sendVideo($message),
                TelegramActions::sendAnimation->value => $this->telegramRequest->sendAnimation($message),
            };

            $user->complete();
            $user->save();
        }

        $message = $this->messageBuilder->buildMessage(chat_id: $job->actor_id, text: "Пост был разослан $count пользователям");
        $this->telegramRequest->sendMessage($message);
        $job->complete();
        $job->save();
    }
}
