<?php

namespace App\Telegram\UseCases;
use App\Telegram\InlineKeyboard\ {
    InlineKeyboard,
    InlineButton
};

class MessageBuilder {

    public function buildMessage(int $chat_id, string $text, ?InlineKeyboard $keyboard = null, ?array $document = null): array {
        $data = ['chat_id' => $chat_id, 'text' => $text];

        if( $keyboard ) {
            $data['reply_markup'] = $keyboard->buildKeyboardData();
        }

        return $data;
    }

    public function buildDocument(int $chat_id, string $caption, string $file_id, ?InlineKeyboard $keyboard = null): array {
        $data = ['chat_id' => $chat_id, 'caption' => $caption, 'document' => $file_id];

        if( $keyboard ) {
            $data['reply_markup'] = $keyboard->buildKeyboardData();
        }

        return $data;
    }
}
