<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MessageUpdate;
use App\Models\State;
use App\Telegram\Enums;

class StateUpdater {

    public function __construct(
        private InlineBuilder $inlineBuilder = new InlineBuilder,
        private MessageBuilder $messageBuilder = new MessageBuilder
    ) {}

    public function handleUpdate(MessageUpdate $update, State $state): bool {
        return match($state->state_id) {
            Enums\States::Create_post->value => $this->handleCreatePost($update, $state),
            default => false
        };
    }

    private function handleCreatePost(MessageUpdate $update, State $state): bool {}
}
