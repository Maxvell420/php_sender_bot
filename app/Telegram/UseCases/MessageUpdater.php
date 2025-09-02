<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MessageUpdate;
use App\Models\User;
use App\Telegram\Updates\Update;

class MessageUpdater extends UpdateHandler
{
    public function handleUpdate(Update $values): void
    {
        /**
         * @var MessageUpdate $values
         */

        $user_id = $values->findMessageFromId();

        if (!$user_id) {
            // Если пришло не от бота то как-то обработать
            return;
        }

        if ($values->hasBotCommands()) {
            $handler = new MsgHandlers\BotCommands();
            $handler->handleUpdate($values);
        } else {
            // Дефолт сообщение
        }
    }
}
