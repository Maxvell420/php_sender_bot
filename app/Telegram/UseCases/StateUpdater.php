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
        if( $update->hasDocument() ) {
            $action = TelegramActions::sendDocument;
            $text = $update->getCaption();
            $entities = $update->getCaptionEntities();
        }
        elseif( $update->hasPhoto() ) {
            $action = TelegramActions::sendPhoto;
            $text = $update->getCaption();
            $entities = $update->getCaptionEntities();
        }
        elseif( $update->hasText() ) {
            $action = TelegramActions::sendMessage;
            $text = $update->findText();
            $entities = $update->getTextEntities();
        }
        else {
            return true;
        }

        if( !empty($entities) ) {
            $text = $this->messageBuilder->buildBeautifulMessage($text, $entities);
        }

        $user_id = $state->actor_id;

        $message = $this->buildPostMessage(TelegramActions::sendMessage, $update, $text, $user_id, ['parse_mode' => 'MarkdownV2']);
        $state->delete();

        $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $message);

        return false;
    }

    private function buildPostMessage(TelegramActions $type, MessageUpdate $update, string $message, int $user_id, array $params = []): array {
        $keyboard = $this->buildCreatePostKeyboard();
        switch($type) {
            case TelegramActions::sendPhoto:
                $photo = $update->getPhoto();
                $file = array_pop($photo);
                $message = $this->messageBuilder->buildPhoto($user_id, $message, $file['file_id'], $keyboard, $params);
                break;

            case TelegramActions::sendDocument:
                $document = $update->getDocument();
                $message = $this->messageBuilder->buildDocument($user_id, $message, $document->file_id, $keyboard, $params);
                break;

            default:
                $message = $this->messageBuilder->buildMessage($user_id, $message, $keyboard, $params);
                break;
        };
        return $message;
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
