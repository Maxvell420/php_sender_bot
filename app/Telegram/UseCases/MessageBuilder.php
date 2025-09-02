<?php

namespace App\Telegram\UseCases;

use App\Telegram\InlineKeyboard\{
    InlineKeyboard,
};

class MessageBuilder
{

    public function buildMessage(int $chat_id, string $text, ?InlineKeyboard $keyboard = null): array
    {
        $data = ['chat_id' => $chat_id, 'text' => $text];
        $data = $this->handleParts($data, $keyboard);
        return $data;
    }

    public function buildDocument(int $chat_id, string $caption, string $file_id, ?InlineKeyboard $keyboard = null): array
    {
        $data = ['chat_id' => $chat_id, 'caption' => $caption, 'document' => $file_id];
        $data = $this->handleParts($data, $keyboard);
        return $data;
    }

    private function handleParts(array $data, ?InlineKeyboard $keyboard = null): array
    {
        if ($keyboard && !$keyboard->isEmpty()) {
            $data['reply_markup'] = json_encode($keyboard->buildKeyboardData());
        }

        return $data;
    }
}
