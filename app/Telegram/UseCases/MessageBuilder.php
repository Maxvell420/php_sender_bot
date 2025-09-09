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

    // private function getMessageEmojis(array $messageLetters, DataCollection $entities): array {
    //     $fault = 0;
    //     $emojis = [];
    //     /**
    //      * @var Entity[] $entities
    //      */

    //     dump($entities);

    //     foreach($entities as $entity) {
    //         if( !$entity->isAllowedType() ) {
    //             continue;
    //         }

    //         if( $entity->type != 'custom_emoji' ) {
    //             continue;
    //         }

    //         $offset = $entity->offset;
    //         $length = $entity->length;
    //         // dump($messageLetters);
    //         $insertArray = range(0, $length - 1);
    //         array_splice($messageLetters, $offset + 1 + $fault, 0, $insertArray);
    //         $emoji = $messageLetters[$offset + $fault];
    //         $fault += $length - 1;
    //         $emojis[] = $emoji;
    //     }

    //     dd($messageLetters) dd($emojis);
    // }

    public function buildBeautifulMessage(string $message, DataCollection $entities): string {
        $events = [];

        $message_array = mb_str_split($message);

        /**
         * @var Entity[] $entities
         */
        $fault = 0;
        // dd($entities);

        foreach($entities as $entity) {
            if( !$entity->isAllowedType() ) {
                continue;
            }

            $offset = $entity->offset;
            $length = $entity->length;

            if( $entity->type == 'custom_emoji' ) {
                $insertArray = [];

                for($i = 0; $i < $length; $i++) {
                    $insertArray[] = '';
                }

                array_splice($message_array, $offset + 1 + $fault, 0, $insertArray);
                $events[$offset + $fault][EntityPosition::Start->value][] = $entity;

                if( isset($events[$offset + $length + $fault][EntityPosition::End->value]) ) {
                    array_unshift($events[$offset + $length + $fault][EntityPosition::End->value], $entity);
                }
                else {
                    $events[$offset + $length + $fault][EntityPosition::End->value][] = $entity;
                }

                $fault += $length - 1;
            }
            else {
                $events[$offset + $fault][EntityPosition::Start->value][] = $entity;
                $events[$offset + $length + $fault][EntityPosition::End->value][] = $entity;
            }
        }

        $beautifulArray = [];
        // dd($message_array);

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
            $offset = 0;

            if( isset($letter_events[EntityPosition::End->value]) ) {
                foreach($letter_events[EntityPosition::End->value] as $event) {
                    [$offset, $letter, $event] = $this->handleEvent(EntityPosition::End, $event, $message_array, $position);
                    $beautifulArray[] = $event;
                }
            }

            if( isset($letter_events[EntityPosition::Start->value]) ) {
                foreach($letter_events[EntityPosition::Start->value] as $event) {
                    [$offset, $letter, $event] = $this->handleEvent(EntityPosition::Start, $event, $message_array, $position);
                    $beautifulArray[] = $event;
                }
            }

            $position += $offset;
            $beautifulArray[] = $letter;
        }

        return implode('', $beautifulArray);
    }

    // Вот это мне не нравится....
    private function handleEvent(EntityPosition $position, Entity $entity, array $message_array, int $eventPosition): array {
        $offset = 0;
        $letter = $message_array[$eventPosition];

        if( $this->isSpecialLetter($letter) ) {
            $letter = '\\' . $letter;
        }

        switch($entity->type) {
            case TelegramEntities::Custom_emoji->value:

                if( $position == EntityPosition::Start ) {
                    $event = $entity->getTypeTags($position);
                    $offset = $entity->length - 1;
                }
                else {
                    $event = $entity->getTypeTags($position);
                    $letter = '';
                }
                break;

            default:
                $event = $entity->getTypeTags($position);
                break;
        }

        return [$offset, $letter, $event];
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
