<?php

namespace App\Telegram\Enums;

enum Commands: string {
    case Start = '/start';
    case Logs = '/logs';
    case Users = '/users';
}
