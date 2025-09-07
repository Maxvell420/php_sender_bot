<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class From extends Data
{

    public function __construct(
        #[Validation\Required, Validation\Numeric]
        public int $id,
        public ?string $username = null,
    ) {}

    public function getUserName(): ?string
    {
        return $this->username;
    }

    public function getUserId(): int
    {
        return $this->id;
    }
}
