<?php

namespace App\Telegram\Enums;

enum ChatType: string {
    case Private = 'private';
    case Group = 'group';
    case Supergroup = 'supergroup';
    case Channel = 'channel';
}