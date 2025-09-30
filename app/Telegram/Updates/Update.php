<?php

namespace App\Telegram\Updates;

use App\Telegram\Enums\UpdateType;

interface Update {

    public function getUpdateId(): int;

    public function getType(): UpdateType;
}
