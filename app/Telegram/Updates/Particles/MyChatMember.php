<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;

class MyChatMember extends Data
{

    public function __construct(
        public Particles\Chat $chat,
        public Particles\From $from,
        public Particles\NewChatMember $new_chat_member
    ) {}
}
