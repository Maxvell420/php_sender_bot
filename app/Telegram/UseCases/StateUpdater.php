<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramRequest;
use App\Telegram\Updates\MessageUpdate;
use App\Models\State;
use App\Telegram\ {
    Enums,
    Values
};
use App\Telegram\InlineKeyboard\InlineKeyboard;

class StateUpdater {

    private TelegramRequest $telegramRequest;

    public function __construct(
        private InlineBuilder $inlineBuilder = new InlineBuilder,
        private MessageBuilder $messageBuilder = new MessageBuilder,
    ) {
        $this->telegramRequest = new TelegramRequest(env('TG_BOT_SECRET'));
    }

    public function handleUpdate(MessageUpdate $update, State $state): bool {
        return match ($state->state_id) {
            Enums\States::Create_post->value => $this->handleCreatePost($update, $state),
            default => false
        };
    }

    private function handleCreatePost(MessageUpdate $update, State $state): bool {
        // Передавать в Request Енам чтобы вызывать функцию Отправить данные
        $handled = false;
        $keyboard = $this->buildCreatePostKeyboard();
        $user_id = $state->actor_id;

        if( $update->hasDocument() ) {
            $document = $update->getDocument();
            $message = $this->messageBuilder->buildDocument($user_id, $update->getCaption(), $document->file_id, $keyboard);
            $this->telegramRequest->sendMessage(TelegramActions::sendDocument, $message);
            $handled = true;
        }
        elseif( $update->hasPhoto() ) {
            $photo = $update->getPhoto();
            $file = array_pop($photo);
            $message = $this->messageBuilder->buildPhoto($user_id, $update->getCaption(), $file['file_id'], $keyboard);
            $this->telegramRequest->sendMessage(TelegramActions::sendPhoto, $message);
            $handled = true;
        }
        elseif( $update->hasText() ) {
            $text = $update->findText();
            $message = $this->messageBuilder->buildMessage($user_id, $text, $keyboard);
            $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $message);
            $handled = true;
        }

        if( $handled ) {
            $state->delete();
        }

        return $handled;
    }

    private function buildCreatePostKeyboard(): InlineKeyboard {
        $yesData = new Values\CallbackDataValues(Enums\Callback::SendPost, 'yes');
        $noData = new Values\CallbackDataValues(Enums\Callback::SendPost, 'no');
        $yesButton = $this->inlineBuilder->buildDataButton('Да', json_encode($yesData));
        $noButton = $this->inlineBuilder->buildDataButton('Нет', json_encode($noData));
        $keyboard = $this->inlineBuilder->buildKeyboard([$yesButton, $noButton]);
        return $keyboard;
    }
}
