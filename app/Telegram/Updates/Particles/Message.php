<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;
use Spatie\LaravelData\DataCollection;

class Message extends Data {

    public function __construct(
        #[Validation\Required,
        Validation\Numeric]
        public int $message_id,
        public Chat $chat,
        public ?From $from = null,
        #[Validation\StringType]
        public ?string $text = null,
        /**
            * @var Entity[]|DataCollection
        */
        public ?DataCollection $entities = null
    ) {}

    public function getUserId(): int {
        return (int) $this->from?->getUserId();
    }

    public function getUserName(): string {
        return $this->from->getUserName();
    }

    public function hasBotCommands(): bool {
        if( !$this->entities ) {
            return false;
        }

        $has_command = false;

        /**
            * @var Entity $entity
        */

        foreach($this->entities as $entity) {
            if( $entity->isCommand() == 'bot_command' ) {
                $has_command = true;
            }
        }

        return $has_command;
    }

    public function findText(): ?string {
        return $this->text;
    }

    public function findMessageFromId(): ?int {
        return $this->from?->getUserId();
    }

    public function getMessageId(): int {
        return $this->message_id;
    }
}
