<?php

namespace App\Telegram\UseCases;

use App\Telegram\InlineKeyboard\{
    InlineKeyboard,
};
use App\Telegram\Updates\Particles\Entity;
use Spatie\LaravelData\DataCollection;

class MessageBuilder
{

    public function buildMessage(int $chat_id, string $text, ?InlineKeyboard $keyboard = null, array $params = []): array
    {
        $data = ['chat_id' => $chat_id, 'text' => $text];
        if (isset($params['parse_mode'])) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard);
        return $data;
    }

    public function buildHileInlineKeyboard(int $message_id, int $user_id, InlineKeyboard $keyboard, array $params = []): array
    {
        $data = ['message_id' => $message_id, 'chat_id' => $user_id];
        if (isset($params['parse_mode'])) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard);
        return $data;
    }

    public function buildDocument(int $chat_id, ?string $caption = null, string $file_id, ?InlineKeyboard $keyboard = null, array $params = []): array
    {
        $data = ['chat_id' => $chat_id, 'document' => $file_id];

        if (isset($params['parse_mode'])) {
            $data['parse_mode'] = 'MarkdownV2';
        }
        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    public function buildPhoto(int $chat_id, ?string $caption = null, string $file_id, ?InlineKeyboard $keyboard = null, array $params = []): array
    {
        $data = ['chat_id' => $chat_id, 'photo' => $file_id];

        if (isset($params['parse_mode'])) {
            $data['parse_mode'] = 'MarkdownV2';
        }
        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    private function handleParts(array $data, ?InlineKeyboard $keyboard = null, ?string $caption = null): array
    {
        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard->buildKeyboardData());
        }

        if ($caption) {
            $data['caption'] = $caption;
        }

        return $data;
    }

    public function buildBeautifulMessage(string $message, DataCollection $entities): string
    {
        /**
         * @var Entity[] $entities
         */

        $events  = [];

        foreach ($entities as $entity) {
            if (!$entity->isAllowedType()) {
                continue;
            }
            $offset = $entity->offset;
            $length = $entity->length;
            $tags = $entity->getTypeTags();
            $events[$offset]['start'][] = $tags['start'];
            $events[$offset + $length]['end'][] = $tags['end'];
        }

        $message_array = mb_str_split($message);
        $beautifulArray = [];
        dd($message_array);
        foreach ($message_array as $key => $letter) {
            if ($this->isSpecialLetter($letter)) {
                $beautifulArray[] = '\\';
            }
            if (!isset($events[$key])) {
                $beautifulArray[] = $letter;
                continue;
            }

            $letter_events = $events[$key];
            dump([$letter, $letter_events, $key]);

            if (isset($letter_events['end'])) {

                foreach ($letter_events['end'] as $event) {
                    $beautifulArray[] = $event;
                }
            }

            if (isset($letter_events['start'])) {
                foreach ($letter_events['start'] as $event) {
                    $beautifulArray[] = $event;
                }
            }
            $beautifulArray[] = $letter;
        }

        return implode('', $beautifulArray);
    }

    private function isSpecialLetter(string $letter): bool
    {
        return in_array($letter, [
            '_',
            '*',
            '[',
            ']',
            '(',
            ')',
            '~',
            '`',
            '>',
            '#',
            '+',
            '-',
            '=',
            '|',
            '{',
            '}',
            '.',
            '!',
            '-'
        ]);
    }
}
