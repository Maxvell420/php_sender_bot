<?php

namespace App\Telegram\Updates;

interface Update
{
    public function getUpdateId(): int;
}
