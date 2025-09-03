<?php

namespace App\Telegram\Updates;

use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;
use App\Telegram\Updates\Particles\Document;
use Spatie\LaravelData\Attributes\Validation;

class CallbackQueryUpdate extends Data implements Update
{

    public function __construct(
        #[
            Validation\Required,
            Validation\Numeric
        ]
        public int $update_id,
        public Particles\CallbackQuery $callback_query
    ) {}

    public function getUserId(): int
    {
        return $this->callback_query->getUserId();
    }

    public function getUpdateId(): int
    {
        return $this->update_id;
    }

    public function hasFrom(): bool
    {
        $from = $this->callback_query->from;
        return isset($from);
    }

    public function hasPhoto(): bool
    {
        return $this->callback_query->hasPhoto();
    }

    public function hasDocument(): bool
    {
        return $this->callback_query->hasDocument();
    }

    public function hasText(): bool
    {
        return $this->callback_query->hasText();
    }

    public function getDocument(): Document
    {
        return $this->callback_query->getDocument();
    }

    public function getPhoto(): array
    {
        return $this->callback_query->getPhoto();
    }

    public function getCaption(): ?string
    {
        return $this->callback_query->getCaption();
    }
}
