<?php

namespace App\Telegram;

use App\Libs\Infra\Context;
use App\Libs\Telegram\TelegramRequest;
use App\Repositories\ {
    BotChannelRepository,
    JobRepository,
    JobUserRepository,
    LogRepository,
    PostRepository,
    StateRepository,
    UpdateRepository,
    UserRepository
};
use App\Telegram\ErrorHandlers\ {
    TelegramBase,
    TelegramMessage,
    Unhandled
};

use App\Telegram\UseCases\ {
    CallbackQueryUpdater,
    ChannelPostUpdater,
    InlineBuilder,
    JobsHandler,
    MessageBuilder,
    MessageUpdater,
    MyChatMemberUpdater,
    StateUpdater,
    Updates
};

class Builder {

    protected(set) TelegramRequest $telegramRequest;
    public function __construct(protected Context $cntx) {
        $this->telegramRequest = $cntx->telegramRequest;
    }

    protected function buildCallbackQueryUpdater(): CallbackQueryUpdater {
        return new CallbackQueryUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder(),
            userRepository:$this->buildUserRepository()
        );
    }

    protected function buildInlineBuilder(): InlineBuilder {
        return new InlineBuilder();
    }

    protected function buildTelegramRequestFacade(): TelegramRequestFacade {
        return new TelegramRequestFacade($this->cntx);
    }

    protected function buildJobsHandler(): JobsHandler {
        return new JobsHandler(
            telegramRequest:$this->buildTelegramRequestFacade(),
            messageBuilder:$this->buildMessageBuilder(),
            msgErrHandler:$this->buildTelegramWrongMessageHandler()
        );
    }

    protected function buildTelegramWrongMessageHandler(): TelegramMessage {
        return new TelegramMessage(userRepository:$this->buildUserRepository(), logRepository:$this->buildLogRepository());
    }

    protected function buildTelegrambaseHandler(): TelegramBase {
        return new TelegramBase(
            updateRepository:$this->buildUpdateRepository(),
            telegramRequest:$this->buildTelegramRequestFacade(),
            messageBuilder:$this->buildMessageBuilder()
        );
    }

    protected function buildMessageBuilder(): MessageBuilder {
        return new MessageBuilder();
    }

    protected function buildChannelPostUpdater(): ChannelPostUpdater {
        return new ChannelPostUpdater(
            messageBuilder:$this->buildMessageBuilder(),
            userRepository:$this->buildUserRepository(),
            stateRepository:$this->buildStateRepository(),
            botChannelRepository:$this->buildBotChannelRepository()
        );
    }

    protected function buildMessageUpdater(): MessageUpdater {
        return new MessageUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder(),
            stateUpdater:$this->buildStateUpdater(),
            userRepository:$this->buildUserRepository(),
            stateRepository:$this->buildStateRepository(),
            logRepository:$this->buildLogRepository()
        );
    }

    protected function buildMyChatMemberUpdater(): MyChatMemberUpdater {
        return new MyChatMemberUpdater(userRepository:$this->buildUserRepository(), botChannelRepository:$this->buildBotChannelRepository());
    }

    protected function buildUpdates(): Updates {
        return new Updates($this->buildTelegramUpdatesFacade(), $this->buildTelegramRequestFacade());
    }

    protected function buildTelegramUpdatesFacade(): TelegramUpdatesFacade {
        return new TelegramUpdatesFacade($this->cntx);
    }

    protected function buildStateUpdater(): StateUpdater {
        return new StateUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder()
        );
    }

    protected function buildErrorHandler(): Unhandled {
        return new Unhandled($this->buildUpdateRepository(), $this->buildLogRepository());
    }

    protected function buildUserRepository(): UserRepository {
        return new UserRepository($this->cntx);
    }

    protected function buildBotChannelRepository(): BotChannelRepository {
        return new BotChannelRepository($this->cntx);
    }

    protected function buildJobUserRepository(): JobUserRepository {
        return new JobUserRepository($this->cntx);
    }

    protected function buildJobRepository(): JobRepository {
        return new JobRepository($this->cntx);
    }

    protected function buildLogRepository(): LogRepository {
        return new LogRepository($this->cntx);
    }

    protected function buildPostRepository(): PostRepository {
        return new PostRepository($this->cntx);
    }

    protected function buildStateRepository(): StateRepository {
        return new StateRepository($this->cntx);
    }

    protected function buildUpdateRepository(): UpdateRepository {
        return new UpdateRepository($this->cntx);
    }
}
