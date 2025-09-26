<?php

namespace App\Telegram;

use App\Models\ {
    Job,
    Update
};

use App\Telegram\Updates\ {
    CallbackQueryUpdate,
    ChannelPostUpdate,
    MessageUpdate,
    MyChatMemberUpdate,
    Update as UpdatesUpdate
};
use App\Telegram\UseCases\Updates;

class TelegramUpdatesFacade extends Builder {

    public function handleMessage(MessageUpdate $update): void {
        $useCase = $this->buildMessageUpdater();
        $useCase->handleUpdate($update);
    }

    public function handleCallback(CallbackQueryUpdate $update): void {
        $useCase = $this->buildCallbackQueryUpdater();
        $useCase->handleUpdate($update);
    }

    public function getNextUpdateId(): int {
        $repo = $this->buildUpdateRepository();
        return $repo->getNextUpdateId();
    }

    public function findFirstJobNotCompleted(): ?Job {
        $repo = $this->buildJobRepository();
        return $repo->findFirstNotCompleted();
    }

    public function getUpdatesCommander(): Updates {
        return $this->buildUpdates();
    }

    public function handleWrongUpdate(UpdatesUpdate $update, string $message): void {
        $handler = $this->buildTelegrambaseHandler();
        $handler->handleErrorUpdate($update, $message);
    }

    public function handleErrorUpdate(array $data, string $message): void {
        $handler = $this->buildErrorHandler();
        $handler->handleErrorUpdate($data, $message);
    }

    public function handleWrongTelegramRequest(UpdatesUpdate $update, string $message, int $status): void {
        $handler = $this->buildTelegramWrongMessageHandler();
        $handler->handleWrongUpdate($update, $status, $message);
    }

    public function persistUpdate(Update $update): void {
        $repo = $this->buildUpdateRepository();
        $repo->persist($update);
    }

    public function handleChannelPost(ChannelPostUpdate $update): void {
        $useCase = $this->buildChannelPostUpdater();
        $useCase->handleUpdate($update);
    }

    public function handleNewChatmember(MyChatMemberUpdate $update): void {
        $useCase = $this->buildMyChatMemberUpdater();
        $useCase->handleUpdate($update);
    }

    public function sendErrorMessage(int $user_id, string $text): void {
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
        $requestFacade = $this->buildTelegramRequestFacade();
        return $requestFacade->getUpdates($update_id, $timeout);
    }
}
