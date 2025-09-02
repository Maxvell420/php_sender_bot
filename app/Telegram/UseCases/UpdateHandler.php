<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\Update as UpdateInterface;

abstract class UpdateHandler
{
    abstract public function handleUpdate(UpdateInterface $data): void;
}
