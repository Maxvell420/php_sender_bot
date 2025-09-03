<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramRequest;
use App\Telegram\Updates\MessageUpdate;
use App\Models\User;
use App\Telegram\Updates\Update;
use App\Telegram\UseCases\MsgHandlers\BotCommands;

class MessageUpdater extends UpdateHandler {

    public function handleUpdate(Update $values): void {
        /**
         * @var MessageUpdate $values
         */

        $user_id = $values->findMessageFromId();

        if( !$user_id ) {
            // Если пришло не от бота то как-то обработать
            return;
        }

        if( $values->hasBotCommands() ) {
            $handler = new MsgHandlers\BotCommands();
            $handler->handleUpdate($values);
        }
        else {
            $builder = new MessageBuilder;
            $message = $builder->buildMessage(
                $user_id,
                "Я всего лишь бот :) Если возникли какие-то вопросы, то переходи в канал. Чтобы получить гайд, просто нажми /start. Все важные анонсы я пришлю сам!"
            );
            $telegramRequest = new TelegramRequest(env('TG_BOT_SECRET'));
            $telegramRequest->sendMessage(TelegramActions::sendMessage, $message);
        }
    }
}
