<?php

namespace App\Telegram;

use App\Libs\Telegram\TelegramActions;
use App\Libs\Telegram\TelegramApiException;

// Это скорее внутренний фасад который будет перехватывать ошибки Telegram и кидать уже сообщения при возможности
class TelegramRequestFacade extends Builder {

    public function sendDocument(array $data): void {
        $this->sendData($data, TelegramActions::sendDocument);
    }

    public function sendMessage(array $data): void {
        $this->sendData($data, TelegramActions::sendMessage);
    }

    public function copyMessage(array $data): void {
        $this->sendData($data, TelegramActions::copyMessage);
    }

    public function sendPhoto(array $data): void {
        $this->sendData($data, TelegramActions::sendPhoto);
    }

    public function sendEditMessageReplyMarkup(array $data): void {
        $this->sendData($data, TelegramActions::editMessageReplyMarkup);
    }

    private function sendData(array $data, TelegramActions $action): void {
        try {
            $this->telegramRequest->sendMessage($action, $data);
        } catch (TelegramApiException $e) {
            $useCase = $this->buildTelegramWrongMessageHandler();
            $useCase->handleTelegramRequest($action, $data, $e->getCode(), $e->getMessage());
        }
    }
}
