<?php

namespace App\Telegram\Updates\Particles;

use App\Telegram\Enums\ChatType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class Chat extends Data {

    public function __construct(
        #[Validation\Required,
        Validation\Numeric]
        public int $id,
        #[Validation\Required,
        Validation\StringType]
        public ChatType $type,
        public ?string $username = null,
    ) {}
}
