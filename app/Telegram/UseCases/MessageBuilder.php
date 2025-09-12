<?php

namespace App\Telegram\UseCases;

use App\Telegram\InlineKeyboard\ {
    InlineKeyboard,
};
use App\Telegram\Updates\Particles\Entity;
use Spatie\LaravelData\DataCollection;
use App\Telegram\Enums\ {
    EntityPosition,
    TelegramEntities
};
use App\Telegram\Exceptions\TelegramBaseException;

class MessageBuilder {

    public function buildMessage(int $chat_id, string $text, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts(data:$data, keyboard:$keyboard, text:$text);
        return $data;
    }

    public function buildHileInlineKeyboard(int $message_id, int $user_id, InlineKeyboard $keyboard, array $params = []): array {
        $data = ['message_id' => $message_id, 'chat_id' => $user_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard);
        return $data;
    }

    public function buildAnimation(int $chat_id, string $file_id, ?string $caption = null, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id, 'animation' => $file_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    public function buildVideo(int $chat_id, string $file_id, ?string $caption = null, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id, 'video' => $file_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    public function buildDocument(int $chat_id, string $file_id, ?string $caption = null, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id, 'document' => $file_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    public function buildPhoto(int $chat_id, string $file_id, ?string $caption = null, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id, 'photo' => $file_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    private function handleParts(array $data, ?InlineKeyboard $keyboard = null, ?string $caption = null, ?string $text = null): array {
        if( $caption ) {
            $length = mb_strlen($caption);

            if( $length > 1024 ) {
                throw new TelegramBaseException('Длина сообщения превышает 1024 символа, текущее значение: ' . $length);
            }

            $data['caption'] = $caption;
        }

        if( $text ) {
            $length = mb_strlen($text);

            if( $length > 4096 ) {
                throw new TelegramBaseException('Длина сообщения превышает 4096 символа, текущее значение: ' . $length);
            }

            $data['text'] = $text;
        }

        if( $keyboard ) {
            $data['reply_markup'] = json_encode($keyboard->buildKeyboardData());
        }

        return $data;
    }

    private function buildMultyButeMessageArray(array $message_array): array {
        for($i = 0; $i < count($message_array); $i++) {
            $letter = $message_array[$i];

            if( !$this->isEmojiWithOrd($letter) ) {
                continue;
            }

            $insertArray = [];

            for($j = 1; $j < 2; $j++) {
                $insertArray[] = '';
            }

            // $fault += strlen($letter);

            array_splice($message_array, $i + 1, 0, $insertArray);
        }

        return $message_array;
    }

    private function isEmojiWithOrd($char) {
        if( strlen($char) === 0 ) return false;
        $code = ord($char[0]);
        return ($code >= 0xF0 && $code <= 0xF4);
    }

    // private function buildEventdependedMessageArray(array $message_array, DataCollection $entities): array
    // {
    //     /**
    //      * @var Entity[] $entities
    //      */

    //     foreach ($entities as $entity) {
    //         if ($entity->type != 'custom_emoji') {
    //             continue;
    //         }

    //         $offset = $entity->offset;
    //         $length = $entity->length;
    //         $insertArray = [];

    //         if ($length == 2) {
    //             $insert_length = 2;
    //         } else {
    //             $insert_length = 2;
    //         }

    //         for ($i = 1; $i < $insert_length; $i++) {
    //             $insertArray[] = '';
    //         }

    //         array_splice($message_array, $offset + 1, 0, $insertArray);
    //     }

    //     return $message_array;
    // }

    private function buildMessageEntitiesEvents(DataCollection $entities, array $message_array): array {
        /**
         * @var Entity[] $entities
         */

        $events = [];

        foreach($entities as $entity) {
            if( !$entity->isAllowedType() ) {
                continue;
            }

            $offset = $entity->offset;
            $length = $entity->length - 1;

            $events[$offset][EntityPosition::Start->value][] = $entity;

            if( $entity->type == 'custom_emoji' && isset($events[$offset + $length][EntityPosition::End->value]) ) {
                array_unshift($events[$offset + $length][EntityPosition::End->value], $entity);
            }
            else {
                $events[$offset + $length][EntityPosition::End->value][] = $entity;
            }

            if( $entity->type == 'blockquote' ) {
                for($i = $offset; $i < $offset + $length; $i++) {
                    $letter = $message_array[$i];

                    if( $letter != "\n" ) {
                        continue;
                    }

                    // Я не до конца понял почему так)
                    $events[$i + 1][EntityPosition::Start->value][] = new Entity('blockquote', 0, 0);
                }
            }
        }

        return $events;
    }

    public function buildCopyMessage(int $chat_id, int $from_chat_id, int $message_id): array {
        return ['chat_id' => $chat_id, 'from_chat_id' => $from_chat_id, 'message_id' => $message_id];
    }

    public function buildBeautifulMessage(string $message, DataCollection $entities): string {
        $events = [];
        $message_array = mb_str_split($message);
        $message_array = $this->buildMultyButeMessageArray($message_array);
        $events = $this->buildMessageEntitiesEvents($entities, $message_array);
        /**
         * @var Entity[] $entities
         */
        $beautifulArray = [];

        for($position = 0; $position < count($message_array); $position++) {
            $letter = $message_array[$position];

            if( !isset($events[$position]) ) {
                $letter = $this->handleSpecialLetter($letter);
                $beautifulArray[] = $letter;
                continue;
            }

            $letter_events = $events[$position];

            if( isset($letter_events[EntityPosition::End->value]) ) {
                $end_events = [];
                $blockQoute = [];

                foreach(array_reverse($letter_events[EntityPosition::End->value]) as $event) {
                    if( $event->type == 'blockquote' ) {
                        $blockQoute[] = $event;
                        continue;
                    }

                    [$letter, $event] = $this->handleEvent(EntityPosition::End, $event, $message_array, $position);
                    $end_events[] = $event;
                }

                foreach($blockQoute as $event) {
                    [$letter, $event] = $this->handleEvent(EntityPosition::End, $event, $message_array, $position);
                    $end_events[] = $event;
                }

                $beautifulArray[] = $letter;
                $beautifulArray = array_merge($beautifulArray, $end_events);
            }

            if( isset($letter_events[EntityPosition::Start->value]) ) {
                $start_events = [];

                foreach($letter_events[EntityPosition::Start->value] as $event) {
                    [$letter, $event] = $this->handleEvent(EntityPosition::Start, $event, $message_array, $position);
                    $start_events[] = $event;
                }

                $beautifulArray = array_merge($beautifulArray, $start_events);
                $beautifulArray[] = $letter;
            }
        }

        return implode('', $beautifulArray);
    }

    private function handleEvent(EntityPosition $position, Entity $entity, array $message_array, int $eventPosition,): array {
        $letter = $message_array[$eventPosition];

        if( $position == EntityPosition::Start ) {
            switch($entity->type) {
                default:$event = $entity->getTypeTags($position);
                break;
            }
        }
        else {
            switch($entity->type) {
                case TelegramEntities::Custom_emoji->value:
                    $event = $entity->getTypeTags($position);
                    $letter = '';
                    break;

                case TelegramEntities::Blockquote->value:
                    $event = $entity->getTypeTags($position);
                    // Проставляю туда где нету пропуска на другую строку т.к. эвент этим должен закрываться
                    if( !isset($message_array[$eventPosition + 1]) || $message_array[$eventPosition + 1] != "\n" ) {
                        $event .= "\n";
                    }
                    break;

                default:
                    $event = $entity->getTypeTags($position);
                    break;
            }
        }

        $letter = $this->handleSpecialLetter($letter);

        return [$letter, $event];
    }

    private function handleSpecialLetter(string $letter): string {
        return match ($letter) {
            '_' => "\\" . $letter,
            '*' => "\\" . $letter,
            '[' => "\\" . $letter,
            ']' => "\\" . $letter,
            '(' => "\\" . $letter,
            ')' => "\\" . $letter,
            '~' => "\\" . $letter,
            '`' => "\\" . $letter,
            '>' => "\\" . $letter,
            '#' => "\\" . $letter,
            '+' => "\\" . $letter,
            '-' => "\\" . $letter,
            '=' => "\\" . $letter,
            '|' => "\\" . $letter,
            '{' => "\\" . $letter,
            '}' => "\\" . $letter,
            '.' => "\\" . $letter,
            '!' => "\\" . $letter,
            '-' => "\\" . $letter,
            default => $letter
        };
    }
}
