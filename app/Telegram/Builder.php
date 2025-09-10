<?php

namespace App\Telegram;
use App\Libs\Telegram\TelegramRequest;
use App\Telegram\UseCases\ {
    CallbackQueryUpdater,
    InlineBuilder,
    JobsHandler,
    MessageBuilder,
    MessageUpdater,
    MyChatMemberUpdater,
    StateUpdater,
    Updates
};

class Builder {

    public function __construct(protected TelegramRequest $telegramRequest) {}

    protected function buildCallbackQueryUpdater(): CallbackQueryUpdater {
        return new CallbackQueryUpdater(
            telegramRequest:$this->telegramRequest,
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder()
        );
    }

    protected function buildInlineBuilder(): InlineBuilder {
        return new InlineBuilder();
    }

    protected function buildTelegramRequestFacade(): TelegramRequestFacade {
        return new TelegramRequestFacade($this->telegramRequest);
    }

    protected function buildJobsHandler(): JobsHandler {
        return new JobsHandler(tgRequestFacade:$this->buildTelegramRequestFacade(), messageBuilder:$this->buildMessageBuilder());
    }

    protected function buildMessageBuilder(): MessageBuilder {
        return new MessageBuilder();
    }

    protected function buildMessageUpdater(): MessageUpdater {
        return new MessageUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder()
        );
    }

    protected function buildMyChatMemberUpdated(): MyChatMemberUpdater {
        return new MyChatMemberUpdater(telegramRequest:$this->buildTelegramRequestFacade());
    }

    protected function buildStateUpdater(): StateUpdater {
        return new StateUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder()
        );
    }

    protected function buildUpdates(): Updates {
        return new Updates($this->buildTelegramRequestFacade());
    }
}