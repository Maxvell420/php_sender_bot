<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;

// Обработка различных ошибок
class TelegramErrosHandler {

    public function handleTelegramRequest(TelegramActions $action, array $data, int $status): void {}

    public function handleWrongUpdate(array $data): void {}

    public function handleCodeError(string $message): void {}

    public function handleException(string $message): void {}
}
