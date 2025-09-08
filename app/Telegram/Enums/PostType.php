<?php

namespace App\Telegram\Enums;

enum PostType: int
{
    case Document = 1;
    case Message = 2;
    case Photo = 3;
}
