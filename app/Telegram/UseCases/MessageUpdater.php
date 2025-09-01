<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MessageUpdate;
use App\Models\User;
use App\Telegram\Updates\Update;

class MessageUpdater extends UpdateHandler {

    protected string $class = MessageUpdate::class;

    public function handleUpdate(array $data): Update {
        // dd($data);
        $values = $this->buildVO($data);
        dd($values);
    }
}
