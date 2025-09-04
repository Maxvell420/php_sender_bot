<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramRequest;
use App\Telegram\Updates\Update as UpdateInterface;

abstract class UpdateHandler {

    public function __construct(public TelegramRequest $telegramRequest) {}
    abstract public function handleUpdate(UpdateInterface $data): void;
}
