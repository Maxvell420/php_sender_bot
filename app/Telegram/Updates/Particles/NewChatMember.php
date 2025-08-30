<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class NewChatMember extends Data
{

    public function __construct(

        #[Validation\Required, Validation\StringType]
        public string $status,
    ) {}
}
