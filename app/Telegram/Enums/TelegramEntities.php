<?php

namespace App\Telegram\Enums;

enum TelegramEntities: string {
    case Bold = 'bold';
    case Italic = 'italic';
    case Underline = 'underline';
    case Strikethrough = 'strikethrough';
    case Code = 'code';
    case Pre = 'pre';
    case Spoiler = 'spoiler';
    case Blockquote = 'blockquote';
    case Custom_emoji = 'custom_emoji';
}