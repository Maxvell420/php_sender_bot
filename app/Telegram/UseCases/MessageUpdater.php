<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MessageUpdate;
use App\Models\User;
use App\Telegram\Updates\Update;

class MessageUpdater extends UpdateHandler {

    protected string $class = MessageUpdate::class;

    public function handleUpdate(array $data): Update {
        /**
            * @var MessageUpdate $values
        */
        $values = $this->buildVO($data);

        $user_id = $values->findMessageFromId();

        if( !$user_id ) {
            // Если пришло не от бота то как-то обработать
            return $values;
        }

        if( $values->hasBotCommands() ) {
            $handler = new MsgHandlers\BotCommands();
            $handler->handleUpdate($values);
        }
        else {
            // Дефолт сообщение
        }

        return $values;
    }
}
