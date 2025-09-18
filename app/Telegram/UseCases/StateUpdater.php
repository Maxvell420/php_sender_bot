<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Telegram\Updates\MessageUpdate;
use App\Models\State;
use App\Telegram\ {
    Enums,
    TelegramRequestFacade,
};

class StateUpdater {

    public function __construct(
        private TelegramRequestFacade $telegramRequest,
        private InlineBuilder $inlineBuilder,
        private MessageBuilder $messageBuilder,
    ) {}

    public function handleUpdate(MessageUpdate $update, State $state): bool {
        return match ($state->state_id) {
            Enums\States::Create_post->value => $this->handleCreatePost($update, $state),
            default => false
        };
    }

    private function handleCreatePost(MessageUpdate $update, State $state): bool {
        if( $update->hasAnimation() ) {
            $action = TelegramActions::sendAnimation;
            $text = $update->getCaption();
            $entities = $update->getCaptionEntities();
        }
        elseif( $update->hasVideo() ) {
            $action = TelegramActions::sendVideo;
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
        // Документ в самом конце для совместимости
        elseif( $update->hasDocument() ) {
            $action = TelegramActions::sendDocument;
            $text = $update->getCaption();
            $entities = $update->getCaptionEntities();
        }
        else {
            return false;
        }

        $have_entities = !empty($entities);

        if( $have_entities ) {
            $text = $this->messageBuilder->buildBeautifulMessage($text, $entities);
        }

        $user_id = $state->actor_id;

        $params = [];

        if( $have_entities ) {
            $params = ['parse_mode' => 'MarkdownV2'];
        }

        $message = $this->buildPostMessage($action, $update, $text, $user_id, $params);
        $data = ['method' => $action->value, 'data' => $message];
        $state->json = json_encode($data);
        $state->save();
        return true;
    }

    private function buildPostMessage(TelegramActions $type, MessageUpdate $update, string $message, int $user_id, array $params = []): array {
        switch($type) {
            case TelegramActions::sendVideo:
                $video = $update->getVideo();
                $file = $video->file_id;
                $message = $this->messageBuilder->buildVideo(chat_id:$user_id, caption:$message, file_id:$file, params:$params);
                break;

            case TelegramActions::sendPhoto:
                $photo = $update->getPhoto();
                $file = array_pop($photo);
                $message = $this->messageBuilder->buildPhoto(chat_id:$user_id, caption:$message, file_id:$file['file_id'], params:$params);
                break;

            case TelegramActions::sendDocument:
                $document = $update->getDocument();
                $message = $this->messageBuilder->buildDocument(chat_id:$user_id, caption:$message, file_id:$document->file_id, params:$params);
                break;

            case TelegramActions::sendAnimation:
                $animation = $update->getAnimation();
                $file = $animation->file_id;
                $message = $this->messageBuilder->buildAnimation($user_id, $animation->file_id, $message, params:$params);
                break;

            default:
                $message = $this->messageBuilder->buildMessage(chat_id:$user_id, text:$message, params:$params);
                break;
        };

        return $message;
    }
}
