<?php

namespace App\Telegram\Updates;

use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;
use App\Telegram\Updates\Particles\Document;
use Spatie\LaravelData\Attributes\Validation;

class MessageUpdate extends Data implements Update
{

    public function __construct(
        #[
            Validation\Required,
            Validation\Numeric
        ]
        public int $update_id,
        public Particles\Message $message
    ) {}

    public function getMessageId(): int
    {
        return $this->message->getMessageId();
    }

    public function hasBotCommands(): bool
    {
        return $this->message->hasBotCommands();
    }

    public function findMessageFromId(): ?int
    {
        return $this->message->findMessageFromId();
    }

    public function getUserName(): ?string
    {
        return $this->message->getUserName();
    }

    public function getUserId(): int
    {
        return $this->message->getUserId();
    }

    public function getUpdateId(): int
    {
        return $this->update_id;
    }

    public function findText(): ?string
    {
        return $this->message->findText();
    }

    public function hasFrom(): bool
    {
        $from = $this->message->from;
        return isset($from);
    }

    public function hasPhoto(): bool
    {
        return $this->message->hasPhoto();
    }

    public function hasDocument(): bool
    {
        return $this->message->hasDocument();
    }

    public function hasText(): bool
    {
        return $this->message->hasText();
    }

    public function getDocument(): Document
    {
        return $this->message->document;
    }

    public function getPhoto(): array
    {
        return $this->message->photo;
    }

    public function getCaption(): ?string
    {
        return $this->message->caption;
    }
}
