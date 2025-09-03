<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramRequest;
use App\Telegram\Updates\MessageUpdate;
use App\Models\State;
use App\Models\User;
use App\Telegram\Enums;
use App\Telegram\InlineKeyboard\InlineKeyboard;
use App\Telegram\Updates\CallbackQueryUpdate;
use App\Telegram\Values\CallbackDataValues;

class CallbackQueryUpdater {

    private TelegramRequest $telegramRequest;

    public function __construct(
        private InlineBuilder $inlineBuilder = new InlineBuilder,
        private MessageBuilder $messageBuilder = new MessageBuilder,
    ) {
        $this->telegramRequest = new TelegramRequest(env('TG_BOT_SECRET'));
    }

    // Здесь будет парсится Update в зависимости от Енама
    //  Прятать клавиатуру не забыть
    public function handleUpdate(CallbackQueryUpdate $update): void {
        $data = $update->getData();

        match ($data->callback) {
            Enums\Callback::SendPost => $this->handleSendPost($update, $data->data),
        };
    }

    private function handleSendPost(CallbackQueryUpdate $update, string $callback): void {
        if( $callback != 'yes' ) {
            return;
        }

        $message_id = $update->getMessageId();

        $user_id = $update->getUserId();

        $hideKeyboardMessage = $this->messageBuilder->buildHileInlineKeyboard($message_id, $user_id, $this->inlineBuilder->buildKeyboard([]));
        $this->telegramRequest->sendMessage(TelegramActions::editMessageReplyMarkup, $hideKeyboardMessage);

        $user = new User;

        $users = $user->listActiveUsers();

        foreach($users as $user) {
            if( $update->hasDocument() ) {
                $document = $update->getDocument();
                $action = TelegramActions::sendDocument;
                $message = $this->messageBuilder->buildDocument($user->tg_id, $update->getCaption(), $document->file_id);
            }
            elseif( $update->hasPhoto() ) {
                $photo = $update->getPhoto();
                $file = array_pop($photo);
                $action = TelegramActions::sendPhoto;
                $message = $this->messageBuilder->buildPhoto($user->tg_id, $update->getCaption(), $file['file_id']);
            }
            elseif( $update->hasText() ) {
                $text = $update->getText();
                $action = TelegramActions::sendMessage;
                $message = $this->messageBuilder->buildMessage($user->tg_id, $text);
            }

            if( isset($action) ) {
                $this->sendPost($message, $action);
            }
        }
    }

    private function sendPost(array $message, TelegramActions $action): void {
        $this->telegramRequest->sendMessage($action, $message);
    }
}
