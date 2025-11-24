<?php

namespace App\Telegram;

use App\Libs\Infra\Context;
use App\Libs\Infra\InnerDemon;
use App\Telegram\UseCases\Updates;

class TelegramSenderDemon extends InnerDemon {

    private int $connections = 0;
    private Updates $updates;

    public function __construct(protected Context $cntx) {
        parent::__construct($cntx);
        $facade = new TelegramUpdatesFacade($this->cntx);
        $this->updates = $facade->getUpdatesCommander();
    }

    protected function run(): void {
        // каждые 40хчисло таймаута от телеги засыпает на 10 секунд чтобы разорвать соединение с телегой
        if( $this->connections > 40 ) {
            sleep(10);
            $this->connections = 0;
        }

        $this->updates->work();
        $this->connections++;
    }

    protected function handleException(string $message): void {
        $this->updates->handleException($message);
    }

    protected function handleError(string $message): void {
        $this->updates->handleError($message);
    }
}
