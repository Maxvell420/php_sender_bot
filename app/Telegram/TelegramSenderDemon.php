<?php

namespace App\Telegram;

use App\Libs\Infra\InnerDemon;
use App\Telegram\UseCases\Updates;

class TelegramSenderDemon extends InnerDemon
{

    private Updates $updates;

    protected function run(): void
    {
        $facade = new TelegramUpdatesFacade($this->cntx);
        $this->updates = $facade->getUpdatesCommander();
        $this->updates->work();
    }

    protected function handleFallback(string $message): void
    {
        $this->updates->handleFallback($message);
    }
}
