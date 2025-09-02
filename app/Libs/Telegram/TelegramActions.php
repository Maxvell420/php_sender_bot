<?php

namespace App\Libs\Telegram;

enum TelegramActions: string
{
    case getUpdates = 'GetUpdates';
    case sendMessage = 'sendMessage';
    case sendDocument = 'sendDocument';
    case sendPhoto = 'sendPhoto';
}
