<?php

namespace App\Telegram;

use App\Libs\Telegram\TelegramActions;

class TelegramRequestFacade extends Builder {

    public function sendDocument(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::sendDocument, $data);
    }

    public function sendMessage(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::sendMessage, $data);
    }

    public function copyMessage(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::copyMessage, $data);
    }

    public function copyMessages(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::copyMessages, $data);
    }

    public function sendPhoto(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::sendPhoto, $data);
    }

    public function sendAnimation(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::sendAnimation, $data);
    }

    public function sendVideo(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::sendVideo, $data);
    }

    public function sendEditMessageReplyMarkup(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::editMessageReplyMarkup, $data);
    }

    public function getUpdates(?int $offset = null, ?int $timeout = 10): array {
        return $this->telegramRequest->getUpdates($offset, $timeout);
    }

    public function sendFile(array $data): void {
        $this->telegramRequest->sendMessage(TelegramActions::sendDocument, $data, ['file' => true]);
    }
}
