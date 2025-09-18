<?php

namespace App\Telegram\Updates\Particles;

use App\Telegram\Enums\ChannelUserStatus;
use Spatie\LaravelData\Data;

class MyChatMember extends Data {

    public function __construct(
        public Chat $chat,
        public From $from,
        public NewChatMember $new_chat_member
    ) {}

    public function getNewStatus(): ChannelUserStatus {
        return $this->new_chat_member->getStatus();
    }

    public function getUserName(): ?string {
        return $this->from->getUserName();
    }

    public function getUserId(): int {
        return $this->from->getUserId();
    }

    public function getNewChatMemberUserId(): int {
        return $this->new_chat_member->user->id;
    }
}
