<?php

namespace App\Telegram\Updates;

interface Update {

    public function getUpdateId(): int;

    public function hasFrom(): bool;

    public function getUserId(): int;
}
