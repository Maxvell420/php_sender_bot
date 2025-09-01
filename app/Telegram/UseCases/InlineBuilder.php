<?php

namespace App\Telegram\UseCases;
use App\Telegram\InlineKeyboard\ {
    InlineKeyboard,
    InlineButton
};

class InlineBuilder {

    public function buildKeyboard(array $buttons): InlineKeyboard {
        $keyboard = new InlineKeyboard;

        foreach($buttons as $button) {
            $keyboard->addButton($button);
        }

        return $keyboard;
    }

    public function buildUrlButton(string $text, string $url): InlineButton {
        $button = new InlineButton;
        $button->text = $text;
        $button->url = $url;
        return $button;
    }
}
