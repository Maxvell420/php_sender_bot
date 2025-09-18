<?php

namespace App\Telegram\Enums;

enum Callback: int {
    case SendPost = 1;
    case CreatePost = 2;
    case CheckPost = 3;
}
