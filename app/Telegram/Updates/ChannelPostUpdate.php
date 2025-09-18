<?php

namespace App\Telegram\Updates;

use App\Telegram\Enums\UpdateType;
use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;

class ChannelPostUpdate extends Data implements Update {

    public function __construct(
        public int $update_id,
        public Particles\ChannelPost $channel_post
    ) {}

    public function getUpdateId(): int {
        return $this->update_id;
    }

    public function hasFrom(): bool {
        return false;
    }

    public function getUserId(): int {
        return 0;
    }

    public function getType(): UpdateType {
        return UpdateType::ChannelPost;
    }

    public function getChatId(): int {
        return $this->channel_post->sender_chat->id;
    }

    public function hasMediaGroup(): bool {
        return (bool) $this->channel_post->media_group_id;
    }

    public function getMediaGroup(): int {
        return $this->channel_post->media_group_id;
    }

    public function getMessageId(): int {
        return $this->channel_post->message_id;
    }
}
