<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;

class ChannelPost extends Data {

    public function __construct(
        public int $message_id,
        public SenderChat $sender_chat,
        public Chat $chat
    ) {}
}
