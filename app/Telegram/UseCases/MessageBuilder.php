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

class MessageBuilder {

    public function buildMessage(int $chat_id, string $text, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id, 'text' => $text];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard);
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

    public function buildDocument(int $chat_id, ?string $caption = null, string $file_id, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id, 'document' => $file_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    public function buildPhoto(int $chat_id, ?string $caption = null, string $file_id, ?InlineKeyboard $keyboard = null, array $params = []): array {
        $data = ['chat_id' => $chat_id, 'photo' => $file_id];

        if( isset($params['parse_mode']) ) {
            $data['parse_mode'] = 'MarkdownV2';
        }

        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    private function handleParts(array $data, ?InlineKeyboard $keyboard = null, ?string $caption = null): array {
        if( $keyboard ) {
            $data['reply_markup'] = json_encode($keyboard->buildKeyboardData());
        }

        if( $caption ) {
            $data['caption'] = $caption;
        }

        return $data;
    }

    private function buildEventdependedMessageArray(array $message_array, DataCollection $entities): array {
        /**
         * @var Entity[] $entities
         */

        foreach($entities as $entity) {
            if( $entity->type != 'custom_emoji' ) {
                continue;
            }

            $offset = $entity->offset;
            $length = $entity->length;
            $insertArray = [];

            if( $length == 2 ) {
                $insert_length = 2;
            }
            else {
                $insert_length = 2;
            }

            for($i = 1; $i < $insert_length; $i++) {
                $insertArray[] = '';
            }

            array_splice($message_array, $offset + 1, 0, $insertArray);
        }

        return $message_array;
    }

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

                    $events[$i + 1][EntityPosition::Start->value][] = new Entity('blockquote', 0, 0);
                }
            }
        }

        return $events;
    }

    public function buildBeautifulMessage(string $message, DataCollection $entities): string {
        $events = [];

        $message_array = mb_str_split($message);
        $message_array = $this->buildEventdependedMessageArray($message_array, $entities);
        $events = $this->buildMessageEntitiesEvents($entities, $message_array);
        /**
         * @var Entity[] $entities
         */


        $beautifulArray = [];

        for($position = 0; $position < count($message_array); $position++) {
            $letter = $message_array[$position];

            if( $this->isSpecialLetter($letter) ) {
                $letter = '\\' . $letter;
            }

            if( !isset($events[$position]) ) {
                $beautifulArray[] = $letter;
                continue;
            }

            $letter_events = $events[$position];

            if( isset($letter_events[EntityPosition::End->value]) ) {
                $end_events = [];

                foreach($letter_events[EntityPosition::End->value] as $event) {
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

    // Вот это мне не нравится....
    private function handleEvent(EntityPosition $position, Entity $entity, array $message_array, int $eventPosition,): array {
        $letter = $message_array[$eventPosition];

        if( $position == EntityPosition::Start ) {
            switch($entity->type) {
                case TelegramEntities::Custom_emoji->value:
                    $event = $entity->getTypeTags($position);
                    // $letter = $message_array[$eventPosition + 1];
                    // $letter = '';
                    break;

                default:
                    $event = $entity->getTypeTags($position);
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

                    if( $message_array[$eventPosition + 1] != "\n" ) {
                        $event .= "\n";
                    }
                    break;

                default:
                    $event = $entity->getTypeTags($position);
                    break;
            }
        }

        if( $this->isSpecialLetter($letter) ) {
            $letter = '\\' . $letter;
        }

        return [$letter, $event];
    }

    // private function handleBlockquote(array $message_array,Entity $entity)

    private function isSpecialLetter(string $letter): bool {
        return in_array(
            $letter,
            [
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
            ]
        );
    }
}
