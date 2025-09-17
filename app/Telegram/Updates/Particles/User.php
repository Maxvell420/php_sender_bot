<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;

class User extends Data {

    public function __construct(
        public int $id,
        public bool $is_bot,
    ) {}
}
