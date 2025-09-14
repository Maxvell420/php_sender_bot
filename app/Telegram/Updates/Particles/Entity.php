<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;
use App\Telegram\Enums\EntityPosition;

class Entity extends Data
{

    public bool $rare_actions = false;

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

    public function getTypeTags(EntityPosition $position): string
    {
        $types = $this->getAllowedTypes();
        return $types[$this->type][$position->value];
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
            'blockquote' => ['start' => ">", 'end' => ""],
            'custom_emoji' => $this->getCustomEmojiTags(),
            'text_link' => $this->getTextLinkTags()
        ];
    }

    private function getTextLinkTags(): array
    {
        return ['start' => "[", 'end' => "]($this->url)"];
    }

    private function getCustomEmojiTags(): array
    {
        return ['start' => "![", 'end' => "](tg://emoji?id=$this->custom_emoji_id)"];
    }

    private function getPreTags(): array
    {
        return ['start' => "```{$this->language}\n", 'end' => "\n```"];
    }
}
