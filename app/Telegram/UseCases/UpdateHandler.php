<?php

namespace App\Telegram\UseCases;

use App\Telegram\TelegramRequestFacade;
use App\Telegram\Updates\Update as UpdateInterface;

abstract class UpdateHandler {

    public function __construct(private TelegramRequestFacade $telegramRequest) {}
    abstract public function handleUpdate(UpdateInterface $data): void;
}
