<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;
use App\Telegram\Enums\EntityPosition;

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

    public function getTypeTags(EntityPosition $position, $letterPosition, $result_array): array
    {
        $letter = $result_array[$letterPosition];
        $types = $this->getAllowedTypes($letter);
        if ($this->type == 'custom_emoji') {
            $letter = '';

            if ($position == EntityPosition::Start) {
                $insertArray = range($letterPosition, $letterPosition + $this->length);
                dd($insertArray);
                array_splice($result_array, $letterPosition + 1, 0, $insertArray);
                $letterPosition += $this->length;
                // dd($letterPosition);
            }
        }
        return [$result_array, $types[$this->type][$position->value], $letter, $letterPosition];
    }

    public function isAllowedType(): bool
    {
        $types = $this->getAllowedTypes();
        return isset($types[$this->type]);
    }

    private function getAllowedTypes(string $letter = ''): array
    {
        return [
            'bold' => ['start' => '*', 'end' => '*'],
            'italic' => ['start' => '_', 'end' => '_'],
            'underline' => ['start' => '__', 'end' => '__'],
            'strikethrough' => ['start' => '~', 'end' => '~'],
            'code' => ['start' => '`', 'end' => '`'],
            'pre' => $this->getPreTags(),
            'spoiler' => ['start' => '||', 'end' => '||'],
            'blockquote' => ['start' => '>', 'end' => ''],
            'custom_emoji' =>  $this->getCustomEmojiTags($letter)
        ];
    }

    private function getCustomEmojiTags(string $fallback): array
    {
        return ['start' => "![$fallback](tg://emoji?id=$this->custom_emoji_id)", 'end' => ''];
    }

    private function getPreTags(): array
    {
        return ['start' => "```{$this->language}\n", 'end' => '\n```'];
    }
}
