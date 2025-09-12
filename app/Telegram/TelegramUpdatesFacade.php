<?php

namespace App\Telegram;

use App\Models\Job;
use App\Telegram\Updates\ {
    CallbackQueryUpdate,
    MessageUpdate,
    MyChatMemberUpdate
};

class TelegramUpdatesFacade extends Builder {

    public function handleMessage(MessageUpdate $update): void {
        $useCase = $this->buildMessageUpdater();
        $useCase->handleUpdate($update);
    }

    public function handleCallback(CallbackQueryUpdate $update): void {
        $useCase = $this->buildCallbackQueryUpdater();
        $useCase->handleUpdate($update);
    }

    public function handleNewChatmember(MyChatMemberUpdate $update): void {
        $useCase = $this->buildMyChatMemberUpdater();
        $useCase->handleUpdate($update);
    }

    public function sendErrorMessage(int $user_id, string $text) {
        $messageBuilder = $this->buildMessageBuilder();
        $message = $messageBuilder->buildMessage($user_id, $text);
        $facade = $this->buildTelegramRequestFacade();
        $facade->sendMessage($message);
    }

    public function handleJob(Job $job): void {
        $jobsHandler = $this->buildJobsHandler();
        $jobsHandler->handleJob($job);
    }

    public function getUpdates(int $update_id, int $timeout): array {
        return $this->telegramRequest->getUpdates($update_id, $timeout);
    }
}
