<?php

namespace App\Telegram\Updates;

use App\Telegram\Enums\UpdateType;
use Spatie\LaravelData\Data;
use App\Telegram\Updates\Particles;
use App\Telegram\Updates\Particles\Document;
use Spatie\LaravelData\Attributes\Validation;
use App\Telegram\Values\CallbackDataValues;
use Spatie\LaravelData\DataCollection;

class CallbackQueryUpdate extends Data implements Update {

    public function __construct(
        #[
        Validation\Required,
        Validation\Numeric]
        public int $update_id,
        public Particles\CallbackQuery $callback_query
    ) {}

    public function getUserId(): int {
        return $this->callback_query->from->getUserId();
    }

    public function getMessageFromId(): int {
        return $this->callback_query->message->from->id;
    }

    public function getUpdateId(): int {
        return $this->update_id;
    }

    public function hasFrom(): bool {
        $from = $this->callback_query->from;
        return isset($from);
    }

    public function getMessageId(): int {
        return $this->callback_query->message->message_id;
    }

    public function hasPhoto(): bool {
        return $this->callback_query->hasPhoto();
    }

    public function hasDocument(): bool {
        return $this->callback_query->hasDocument();
    }

    public function getCaptionEntities(): ?DataCollection {
        return $this->callback_query->message->caption_entities;
    }

    public function getTextEntities(): ?DataCollection {
        return $this->callback_query->message->entities;
    }

    public function hasText(): bool {
        return $this->callback_query->hasText();
    }

    public function getText(): string {
        return $this->callback_query->message->text;
    }

    public function getDocument(): Document {
        return $this->callback_query->getDocument();
    }

    public function getPhoto(): array {
        return $this->callback_query->getPhoto();
    }

    public function getCaption(): ?string {
        return $this->callback_query->getCaption();
    }

    public function getData(): CallbackDataValues {
        return $this->callback_query->data;
    }

    public function getType(): UpdateType {
        return UpdateType::CallbackQuery;
    }
}
