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

    public function __construct(
        private TelegramRequest $telegramRequest,
        private InlineBuilder $inlineBuilder = new InlineBuilder,
        private MessageBuilder $messageBuilder = new MessageBuilder,
    ) {}

    public function handleUpdate(MessageUpdate $update, State $state): bool {
        return match ($state->state_id) {
            Enums\States::Create_post->value => $this->handleCreatePost($update, $state),
            default => false
        };
    }

    private function handleCreatePost(MessageUpdate $update, State $state): bool {
        $not_handled = true;
        $keyboard = $this->buildCreatePostKeyboard();
        $user_id = $state->actor_id;

        if( $update->hasDocument() ) {
            $document = $update->getDocument();
            $message = $this->messageBuilder->buildDocument($user_id, $update->getCaption(), $document->file_id, $keyboard);
            $this->telegramRequest->sendMessage(TelegramActions::sendDocument, $message);
            $not_handled = false;
        }
        elseif( $update->hasPhoto() ) {
            $photo = $update->getPhoto();
            $file = array_pop($photo);
            $message = $this->messageBuilder->buildPhoto($user_id, $update->getCaption(), $file['file_id'], $keyboard);
            $this->telegramRequest->sendMessage(TelegramActions::sendPhoto, $message);
            $not_handled = false;
        }
        elseif( $update->hasText() ) {
            $text = $update->findText();
            $message = $this->messageBuilder->buildMessage($user_id, $text, $keyboard);
            $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $message);
            $not_handled = false;
        }

        if( !$not_handled ) {
            $state->delete();
        }

        return $not_handled;
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
