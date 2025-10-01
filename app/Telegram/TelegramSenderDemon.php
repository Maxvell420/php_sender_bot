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

    protected function handleException(string $message): void
    {
        $this->updates->handleException($message);
    }

    protected function handleError(string $message): void
    {
        $this->updates->handleError($message);
    }
}
