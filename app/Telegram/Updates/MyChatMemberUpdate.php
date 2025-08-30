<?php

namespace App\Telegram\Updates;

use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;
use Spatie\LaravelData\Attributes\Validation;

class MyChatMemberUpdate extends Data
{
    public function __construct(
        #[Validation\Required, Validation\Numeric]
        public int $update_id,
        public Particles\MyChatMember $my_chat_member
    ) {}
}
