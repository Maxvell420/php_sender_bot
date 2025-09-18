<?php

namespace App\Telegram\Enums;

enum ChannelUserStatus: string {
    case Administrator = 'administrator';
    case Member = 'member';
    case Restricted = 'restricted';
    case Left = 'left';
    case Kicked = 'kicked';
}