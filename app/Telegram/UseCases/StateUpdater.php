<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Telegram\Updates\MessageUpdate;
use App\Models\State;
use App\Telegram\ {
    Enums,
    TelegramRequestFacade,
};
use App\Telegram\Exceptions\TelegramBaseException;

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
        if( $state->json ) {
            return $this->buildMultipleFilesPost($update, $state);
        }

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

        match ($action) {
            TelegramActions::sendDocument => $this->telegramRequest->sendDocument($message),
            TelegramActions::sendMessage => $this->telegramRequest->sendMessage($message),
            TelegramActions::sendPhoto => $this->telegramRequest->sendPhoto($message),
            TelegramActions::sendAnimation => $this->telegramRequest->sendAnimation($message),
            TelegramActions::sendVideo => $this->telegramRequest->sendVideo($message)
        };

        if( in_array($action, [TelegramActions::sendDocument, TelegramActions::sendPhoto, TelegramActions::sendVideo]) ) {
            $type = match($action) {
                TelegramActions::sendDocument => 'document',
                TelegramActions::sendPhoto => 'photo',
                TelegramActions::sendVideo => 'video'
            };
            $data = ['update' => $message, 'type' => $type, 'method' => $action->value];

            $state->json = json_encode($data);
            $state->save();
        }
        else {
            $state->delete();
        }

        return true;
    }

    private function buildMultipleFilesPost(MessageUpdate $update, State $state): bool {
        $json = json_decode($state->json, true);
        $type = $json['type'];
        $method = $json['method'];
        $stateUpdate = $json['update'];

        if( isset($stateUpdate['media']) && count($stateUpdate['media']) == 10 ) {
            $user_id = $state->actor_id;
            $message = $this->messageBuilder->buildMessage(
                $user_id,
                'Загружено максимальное количество приложений'
            );
            $this->telegramRequest->sendMessage($message);
            return true;
        }

        if( $method != TelegramActions::sendMediaGroup->value ) {
            return $this->handlePre($state, $update, $stateUpdate);
        }
        elseif( $update->hasDocument() || $update->hasPhoto() || $update->hasVideo() ) {
            if( $update->hasDocument() ) {
                if( $type != 'document' ) {
                    throw new TelegramBaseException(
                        'Нельзя делать массовую рассылку разных типов данных, только файлы к файлам, фотки к фоткам или к видео :)'
                    );
                }

                $document = $update->getDocument();
                $file = $document->file_id;
                $file_type = 'document';
            }
            elseif( $update->hasPhoto() ) {
                if( !in_array($type, ['photo', 'video']) ) {
                    throw new TelegramBaseException(
                        'Нельзя делать массовую рассылку разных типов данных, только файлы к файлам, фотки к фоткам или к видео :)'
                    );
                }

                $photo = $update->getPhoto();
                $file = array_pop($photo)['file_id'];
                $file_type = 'photo';
            }
            else {
                if( !in_array($type, ['photo', 'video']) ) {
                    throw new TelegramBaseException(
                        'Нельзя делать массовую рассылку разных типов данных, только файлы к файлам, фотки к фоткам или к видео :)'
                    );
                }

                $video = $update->getVideo();
                $file = $video->file_id;
                $file_type = 'video';
            }

            $text = $update->getCaption();
            $entities = $update->getCaptionEntities();

            $beautiful = $entities && $text;

            if( $beautiful ) {
                $text = $this->messageBuilder->buildBeautifulMessage($text, $entities);
            }

            if( isset($state_update['parse_mode']) ) {
                $params = ['parse_mode' => 'MarkdownV2'];
            }

            $file_data = ['media' => $file, 'type' => $file_type];

            if( $text ) {
                $file_data['caption'] = $text;
            }

            if( isset($params['parse_mode']) ) {
                $stateUpdate['parse_mode'] = 'MarkdownV2';
            }

            $stateUpdate['media'][] = $file_data;
            $state->json = json_encode(['update' => $stateUpdate, 'type' => $type, 'method' => $method]);
            $state->save();

            return true;
        }
        else {
            $user_id = $state->actor_id;
            $this->sendWrongInnerParticlesMessage($user_id);
            return true;
        }
    }

    private function sendWrongInnerParticlesMessage(int $user_id): void {
        $message = $this->messageBuilder->buildMessage(
            $user_id,
            'Нельзя делать массовую рассылку разных типов данных, только файлы к файлам, фотки к фоткам или к видео :)'
        );
        $this->telegramRequest->sendMessage($message);
    }

    private function handlePre(State $state, MessageUpdate $update, array $state_update): bool {
        $method = TelegramActions::sendMediaGroup->value;

        if( $update->hasDocument() && isset($state_update['document']) ) {
            $document = $update->getDocument();
            $caption = $update->getCaption();
            $entities = $update->getCaptionEntities();

            if( !empty($entities) && $caption ) {
                $caption = $this->messageBuilder->buildBeautifulMessage($caption, $entities);
            }

            $file = $document->file_id;
            $type = 'document';

            $file_data = ['type' => $type, 'media' => $file];

            if( $caption ) {
                $file_data['caption'] = $caption;
            }

            $old_file_data = ['type' => $type, 'media', $state_update['document']];

            if( isset($state_update['caption']) ) {
                $old_file_data['caption'] = $state_update['caption'];
            }

            $message = $this->messageBuilder->buildMediaGroup($state->actor_id, [$old_file_data, $file_data]);
            $data = ['type' => 'document', 'update' => $message, 'method' => $method];
            $state->json = json_encode($data);
            $state->save();
            return true;
        }
        elseif( ($update->hasPhoto() || $update->hasVideo()) && (isset($state_update['photo']) || isset($state_update['video'])) ) {
            $entities = $update->getCaptionEntities();
            $caption = $update->getCaption();

            if( $update->hasPhoto() ) {
                $photo = $update->getPhoto();
                $file = array_pop($photo)['file_id'];
                $type = 'photo';
            }
            else {
                $video = $update->getVideo();
                $file = $video->file_id;
                $type = 'video';
            }

            $beautiful = !empty($entities) && $caption;

            if( $beautiful ) {
                $caption = $this->messageBuilder->buildBeautifulMessage($caption, $entities);
            }

            $file_data = ['type' => $type, 'media' => $file];

            if( $caption ) {
                $file_data['caption'] = $caption;
            }

            $old_file_data = ['type' => $type, 'media' => $file];

            if( isset($state_update['caption']) ) {
                $old_file_data['caption'] = $state_update['caption'];
            }

            if( isset($state_update['parse_mode']) || $beautiful ) {
                $params = ['parse_mode' => 'MarkdownV2'];
            }
            else {
                $params = [];
            }

            $message = $this->messageBuilder->buildMediaGroup($state->actor_id, [$old_file_data, $file_data], params:$params);

            $data = ['type' => $type, 'update' => $message, 'method' => $method];
            $state->json = json_encode($data);
            $state->save();
            return true;
        }
        else {
            $this->sendWrongInnerParticlesMessage($state->actor_id);
            return true;
        }
    }

    private function buildPostMessage(TelegramActions $type, MessageUpdate $update, string $message, int $user_id, array $params = []): array {
        $keyboard = $this->messageBuilder->buildCreatePostKeyboard();
        switch($type) {
            case TelegramActions::sendVideo:
                $video = $update->getVideo();
                $file = $video->file_id;
                $message = $this->messageBuilder->buildVideo(chat_id:$user_id, caption:$message, file_id:$file, keyboard:$keyboard, params:$params);
                break;

            case TelegramActions::sendPhoto:
                $photo = $update->getPhoto();
                $file = array_pop($photo);
                $message = $this->messageBuilder->buildPhoto(chat_id:$user_id, caption:$message, file_id:$file['file_id'], keyboard:$keyboard, params:$params);
                break;

            case TelegramActions::sendDocument:
                $document = $update->getDocument();
                $message = $this->messageBuilder->buildDocument(chat_id:$user_id, caption:$message, file_id:$document->file_id, keyboard:$keyboard, params:$params);
                break;

            case TelegramActions::sendAnimation:
                $animation = $update->getAnimation();
                $file = $animation->file_id;
                $message = $this->messageBuilder->buildAnimation($user_id, $animation->file_id, $message, $keyboard, $params);
                break;

            default:
                $message = $this->messageBuilder->buildMessage(chat_id:$user_id, text:$message, keyboard:$keyboard, params:$params);
                break;
        };

        return $message;
    }
}
