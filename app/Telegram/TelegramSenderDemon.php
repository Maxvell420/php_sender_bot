<?php

namespace App\Telegram;

use App\Libs\Infra\Context;
use App\Libs\Infra\InnerDemon;
use App\Telegram\UseCases\Updates;

class TelegramSenderDemon extends InnerDemon
{

    private Updates $updates;

    public function __construct(protected Context $cntx)
    {
        parent::__construct($cntx);
        $facade = new TelegramUpdatesFacade($this->cntx);
        $this->updates = $facade->getUpdatesCommander();
    }

    protected function run(): void
    {

        $this->updates->work();
    }

    protected function handleFallback(string $message): void
    {
        $this->updates->handleFallback($message);
    }
}
