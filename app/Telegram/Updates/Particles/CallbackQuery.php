<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use App\Telegram\Values\CallbackDataValues;

class CallbackQuery extends Data
{
    // поменять эти все обьекты на норм структуру определится где интерфесы хранить
    public function __construct(
        public int $id,
        public From $from,
        public Message $message,
        public CallbackDataValues $data,
        public int $chat_instance
    ) {}

    public function getUserId(): int
    {
        return $this->message->getUserId();
    }


    public function hasFrom(): bool
    {
        $from = $this->message->from;
        return isset($from);
    }

    public function hasPhoto(): bool
    {
        return (bool) $this->message->photo;
    }

    public function hasDocument(): bool
    {
        return (bool) $this->message->document;
    }

    public function hasText(): bool
    {
        return (bool) $this->message->text;
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
