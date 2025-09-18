<?php

namespace App\Telegram\Updates\Particles;

use App\Telegram\Enums\ChannelUserStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class NewChatMember extends Data {

    public function __construct(

        #[Validation\Required,
        Validation\StringType]
        public ChannelUserStatus $status,
        public User $user
    ) {}

    public function getStatus(): ChannelUserStatus {
        return $this->status;
    }
}
