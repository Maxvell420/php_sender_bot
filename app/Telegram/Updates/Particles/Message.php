<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class Message extends Data
{
    public function __construct(
        #[Validation\Required, Validation\Numeric]
        public int $message_id,
        public Chat $chat,
        public ?From $from = null,
        #[Validation\StringType]
        public ?string $text = null,
        public ?Entities $entities = null
    ) {}

    public function isBotCommand(): bool
    {
        return $this?->entities->type == 'bot_command';
    }

    public function getMessageId(): int
    {
        return $this->message_id;
    }
}
