<?php

namespace App\Telegram\Updates;

use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;
use Spatie\LaravelData\Attributes\Validation;

class MessageUpdate extends Data
{
    public function __construct(
        #[Validation\Required, Validation\Numeric]
        public int $update_id,
        public Particles\Message $message
    ) {}

    public function getMessageId(): int
    {
        return $this->message->getMessageId();
    }
}
