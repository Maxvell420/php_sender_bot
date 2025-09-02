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

    public function buildDocument(int $chat_id, ?string $caption = null, string $file_id, ?InlineKeyboard $keyboard = null): array
    {
        $data = ['chat_id' => $chat_id, 'document' => $file_id];
        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    public function buildPhoto(int $chat_id, ?string $caption = null, string $file_id, ?InlineKeyboard $keyboard = null): array
    {
        $data = ['chat_id' => $chat_id, 'photo' => $file_id];
        $data = $this->handleParts($data, $keyboard, $caption);
        return $data;
    }

    private function handleParts(array $data, ?InlineKeyboard $keyboard = null, ?string $caption = null): array
    {
        if ($keyboard && !$keyboard->isEmpty()) {
            $data['reply_markup'] = json_encode($keyboard->buildKeyboardData());
        }

        if ($caption) {
            $data['caption'] = $caption;
        }

        return $data;
    }
}
