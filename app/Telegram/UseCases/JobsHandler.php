<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Models\ {
    Job,
    JobUser
};
use App\Telegram\Enums;
use App\Telegram\TelegramRequestFacade;

class JobsHandler {

    public function __construct(private TelegramRequestFacade $telegramRequest, private MessageBuilder $messageBuilder) {}

    public function handleJob(Job $job): void {
        match ($job->job_type) {
            Enums\JobTypes::Create_post->value => $this->handleSendPost($job),
        };
    }

    private function handleSendPost(Job $job): void {
        $count = 0;

        $update = json_decode($job->json, true);

        foreach(TelegramActions::cases() as $case) {
            if( $case->value == $update['action'] ) {
                $action = $case;
            }
        }

        $userJobs = new JobUser();
        $userJobs = $userJobs->listByJob($job->id);
        $actor_id = $job->actor_id;
        /**
         * @var JobUser[] $userJobs
         */

        foreach($userJobs as $user) {
            $count++;

            if( $user->isCompleted() ) {
                continue;
            }

            if( $user->actor_id == $actor_id ) {
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
