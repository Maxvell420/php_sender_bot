<?php

namespace App\Telegram\UseCases;

use App\Telegram\InlineKeyboard\{
    InlineKeyboard,
};
use App\Telegram\Updates\Particles\Entity;
use Spatie\LaravelData\DataCollection;
use App\Telegram\Enums\EntityPosition;

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
            $events[$offset][EntityPosition::Start->value][] = $entity;
            $events[$offset + $length][EntityPosition::End->value][] = $entity;
        }

        $message_array = mb_str_split($message);

        $beautifulArray = [];
        for ($position = 0; $position < count($message_array); $position++) {
            $letter = $message_array[$position];
            if ($this->isSpecialLetter($letter)) {
                $beautifulArray[] = '\\';
            }

            if (!isset($events[$position])) {
                $beautifulArray[] = $letter;
                continue;
            }

            $letter_events = $events[$position];

            if (isset($letter_events[EntityPosition::End->value])) {

                foreach ($letter_events[EntityPosition::End->value] as $event) {
                    [$message_array, $event, $letter, $position] = $event->getTypeTags(EntityPosition::End, $position, $message_array);
                    $beautifulArray[] = $event;
                }
            }

            $beautifulArray[] = $letter;

            if (isset($letter_events[EntityPosition::Start->value])) {

                foreach ($letter_events[EntityPosition::Start->value] as $event) {
                    [$message_array, $event, $letter, $position] = $event->getTypeTags(EntityPosition::Start, $position, $message_array);
                    $beautifulArray[] = $event;
                }
            }
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
