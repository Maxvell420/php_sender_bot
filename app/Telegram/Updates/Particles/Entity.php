<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class Entity extends Data {

    public function __construct(
        #[Validation\Required,
        Validation\StringType]
        public string $type
    ) {}

    public function isCommand(): bool {
        return $this->type == 'bot_command';
    }
}
