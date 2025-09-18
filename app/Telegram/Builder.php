<?php

namespace App\Telegram;

use App\Libs\Telegram\TelegramRequest;
use App\Telegram\UseCases\ {
    CallbackQueryUpdater,
    ChannelPostUpdater,
    InlineBuilder,
    JobsHandler,
    MessageBuilder,
    MessageUpdater,
    MyChatMemberUpdater,
    StateUpdater,
    TelegramWrongMessagesHandler,
    Updates
};

class Builder {

    public function __construct(protected TelegramRequest $telegramRequest) {}

    protected function buildCallbackQueryUpdater(): CallbackQueryUpdater {
        return new CallbackQueryUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
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
        return new JobsHandler(telegramRequest:$this->buildTelegramRequestFacade(), messageBuilder:$this->buildMessageBuilder());
    }

    protected function buildMessageBuilder(): MessageBuilder {
        return new MessageBuilder();
    }

    protected function buildChannelPostUpdater(): ChannelPostUpdater {
        return new ChannelPostUpdater();
    }

    protected function buildMessageUpdater(): MessageUpdater {
        return new MessageUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder(),
            stateUpdater:$this->buildStateUpdater()
        );
    }

    protected function buildMyChatMemberUpdater(): MyChatMemberUpdater {
        return new MyChatMemberUpdater();
    }

    protected function buildStateUpdater(): StateUpdater {
        return new StateUpdater(
            telegramRequest:$this->buildTelegramRequestFacade(),
            inlineBuilder:$this->buildInlineBuilder(),
            messageBuilder:$this->buildMessageBuilder()
        );
    }

    protected function buildUpdates(): Updates {
        return new Updates($this->telegramRequest);
    }

    protected function buildTelegramWrongMessageHandler(): TelegramWrongMessagesHandler {
        return new TelegramWrongMessagesHandler();
    }
}
