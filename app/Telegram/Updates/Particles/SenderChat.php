<?php

namespace App\Telegram\Updates\Particles;

use App\Telegram\Enums\ChatType;
use Spatie\LaravelData\Data;

class SenderChat extends Data {

    public function __construct(
        public int $id,
        public ChatType $type
    ) {}
}
