<?php

namespace App\Telegram;

use App\Http\Exceptions\TelegramApiException;
use App\Libs\Telegram\TelegramActions;

// Это скорее внутренний фасад который будет перехватывать ошибки Telegram и кидать уже сообщения при возможности
class TelegramRequestFacade extends Builder {

    // Сделать в 1 функцию где и обернуть в tryCatch
    public function sendDocument(array $data) {
        $this->sendData($data, TelegramActions::sendDocument);
    }

    public function sendMessage() {}

    public function sendPhoto() {}

    public function sendEditMessageReplyMarkup() {}

    private function sendData(array $data, TelegramActions $action): void {
        try {
            $this->telegramRequest->sendMessage($action, $data);
        } catch (TelegramApiException $e) {
            
        }
    }
}
