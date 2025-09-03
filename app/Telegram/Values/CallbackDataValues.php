<?php

namespace App\Telegram\Values;

use App\Telegram\Enums;
use Spatie\LaravelData\Data;

class CallbackDataValues extends Data
{

    public function __construct(
        public Enums\Callback $callback,
        public string $data
    ) {}
}
