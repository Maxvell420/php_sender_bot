<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class Chat extends Data
{

    public function __construct(
        #[Validation\Required, Validation\Numeric]
        public int $id,
        #[Validation\Required, Validation\StringType]
        public string $username,
    ) {}
}
