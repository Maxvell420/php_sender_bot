<?php

namespace App\Telegram\Updates;

use App\Telegram\Enums\UpdateType;

interface Update {

    public function getUpdateId(): int;

    public function hasFrom(): bool;

    public function getUserId(): int;

    public function getType(): UpdateType;
}
