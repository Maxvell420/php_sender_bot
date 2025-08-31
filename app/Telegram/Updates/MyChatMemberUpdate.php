<?php

namespace App\Telegram\Updates;

use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;
use Spatie\LaravelData\Attributes\Validation;

class MyChatMemberUpdate extends Data implements Update
{
    public function __construct(
        #[Validation\Required, Validation\Numeric]
        public int $update_id,
        public Particles\MyChatMember $my_chat_member
    ) {}

    public function isMember(): bool
    {
        return $this->my_chat_member->getNewStatus() == 'member';
    }

    public function getUserName(): string
    {
        return $this->my_chat_member->getUserName();
    }

    public function getUserId(): int
    {
        return $this->my_chat_member->getUserId();
    }

    public function getUpdateId(): int
    {
        return $this->update_id;
    }
}
