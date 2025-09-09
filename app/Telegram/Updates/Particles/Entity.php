<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class Entity extends Data
{

    public function __construct(
        #[
            Validation\Required,
            Validation\StringType
        ]
        public string $type,
        public ?int $offset = null,
        public ?int $length = null,
        public ?string $language = null,
        public ?string $url = null,
        public ?int $custom_emoji_id = null
    ) {}

    public function isCommand(): bool
    {
        return $this->type == 'bot_command';
    }

    public function getTypeTags(): array
    {
        $types = $this->getAllowedTypes();
        return $types[$this->type];
    }

    public function isAllowedType(): bool
    {
        $types = $this->getAllowedTypes();
        return isset($types[$this->type]);
    }

    private function getAllowedTypes(): array
    {
        return [
            'bold' => ['start' => '*', 'end' => '*'],
            'italic' => ['start' => '_', 'end' => '_'],
            'underline' => ['start' => '__', 'end' => '__'],
            'strikethrough' => ['start' => '~', 'end' => '~'],
            'code' => ['start' => '`', 'end' => '`'],
            'pre' => $this->getPreTags(),
            'spoiler' => ['start' => '||', 'end' => '||'],
            'blockquote' => ['start' => '>', 'end' => '']
            // custom_emoji
        ];
    }

    private function getPreTags(): array
    {
        return ['start' => "```{$this->language}\n", 'end' => '\n```'];
    }
}
