<?php

namespace App\Libs\Telegram;

enum TelegramActions: string {
    case getUpdates = 'GetUpdates';
    case sendMessage = 'sendMessage';
    case sendDocument = 'sendDocument';
    case sendPhoto = 'sendPhoto';
    case editMessageReplyMarkup = 'editMessageReplyMarkup';
    case copyMessage = 'copyMessage';
    case sendVideo = 'sendVideo';
    case sendAnimation = 'sendAnimation';
    case copyMessages = 'copyMessages';
}
