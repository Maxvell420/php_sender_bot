<?php

namespace App\Telegram\Updates;

use App\Telegram\Enums\ChannelUserStatus;
use App\Telegram\Enums\ChatType;
use App\Telegram\Enums\UpdateType;
use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;
use Spatie\LaravelData\Attributes\Validation;

class MyChatMemberUpdate extends Data implements Update {

    public function __construct(
        #[Validation\Required,
        Validation\Numeric]
        public int $update_id,
        public Particles\MyChatMember $my_chat_member
    ) {}

    public function isMember(): bool {
        return in_array($this->my_chat_member->getNewStatus(), [ChannelUserStatus::Administrator, ChannelUserStatus::Member]);
    }

    public function getUserName(): string {
        return $this->my_chat_member->getUserName();
    }

    public function getUserId(): int {
        return $this->my_chat_member->getUserId();
    }

    public function getUpdateId(): int {
        return $this->update_id;
    }

    public function hasFrom(): bool {
        $from = $this->my_chat_member->from;
        return isset($from);
    }

    public function getChatId(): int {
        return $this->my_chat_member->chat->id;
    }

    public function getType(): UpdateType {
        return UpdateType::MyChatMember;
    }

    public function getNewChatMemberUserId(): int {
        return $this->my_chat_member->getNewChatMemberUserId();
    }

    public function getNewStatus(): ChannelUserStatus {
        return $this->my_chat_member->getNewStatus();
    }

    public function getChatType(): ChatType {
        return $this->my_chat_member->chat->type;
    }
}
