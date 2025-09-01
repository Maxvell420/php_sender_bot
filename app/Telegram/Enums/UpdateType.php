<?php

namespace App\Telegram\Enums;

enum UpdateType: string {
    case MyChatMember = 'my_chat_member';
    case Message = 'message';
}
