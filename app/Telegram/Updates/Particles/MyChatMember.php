<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;

class MyChatMember extends Data
{

    public function __construct(
        public Chat $chat,
        public From $from,
        public NewChatMember $new_chat_member
    ) {}

    public function getNewStatus(): string
    {
        return $this->new_chat_member->getStatus();
    }

    public function getUserName(): ?string
    {
        return $this->from->getUserName();
    }

    public function getUserId(): int
    {
        return $this->from->getUserId();
    }
}
