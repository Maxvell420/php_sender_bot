<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramRequest;
use App\Models\{
    Job,
    JobUser
};
use App\Telegram\Enums;

class JobsHandler
{

    public function __construct(private TelegramRequest $telegramRequest, private MessageBuilder $messageBuilder = new MessageBuilder) {}

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

        foreach (TelegramActions::cases() as $case) {
            if ($case->value == $update['action']) {
                $action = $case;
            }
        }

        $userJobs = new JobUser();
        $userJobs = $userJobs->listByJob($job->id);

        /**
         * @var JobUser[] $userJobs
         */

        foreach ($userJobs as $user) {
            $count++;

            if ($user->isCompleted()) {
                continue;
            }

            $update['message']['chat_id'] = $user->actor_id;

            $this->telegramRequest->sendMessage($action, $update['message']);
            $user->complete();
            $user->save();
        }

        $message = $this->messageBuilder->buildMessage($job->actor_id, "Пост был разослан $count пользователям");
        $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $message);
        $job->complete();
        $job->save();
    }
}
