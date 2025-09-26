<?php

namespace App\Telegram;

use App\Libs\Infra\InnerDemon;
use App\Libs\Telegram\TelegramRequest;
use App\Models\ {
    Update,
    Job
};
use App\Telegram\UseCases\Updates;
use Exception;
use App\Telegram\UseCases\JobsHandler;

class TelegramSenderDemon extends InnerDemon {

    public function run(): void {
        $useCase = new Updates($telegram);
        $useCase->work();
    }

    protected function handleFallback(): void {}
}
