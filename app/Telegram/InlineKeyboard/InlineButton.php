<?php

namespace App\Telegram\InlineKeyboard;

class InlineButton {

    public string $text;
    public ?string $url = null;
    public ?string $callback_data = null;
}