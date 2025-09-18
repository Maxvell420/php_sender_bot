<?php

namespace App\Telegram\UseCases;

use App\Telegram\InlineKeyboard\ {
    InlineKeyboard,
    InlineButton
};
use App\Telegram\ {
    Values,
    Enums
};

class InlineBuilder {

    public function buildKeyboard(array $buttons): InlineKeyboard {
        $keyboard = new InlineKeyboard;

        foreach($buttons as $button) {
            $keyboard->addButton($button);
        }

        return $keyboard;
    }

    public function buildUrlButton(string $text, string $url): InlineButton {
        $button = new InlineButton;
        $button->text = $text;
        $button->url = $url;
        return $button;
    }

    public function buildDataButton(string $text, string $data): InlineButton {
        $button = new InlineButton;
        $button->text = $text;
        $button->callback_data = $data;
        return $button;
    }

    public function buildCreatePostKeyboard(): InlineKeyboard {
        $yesData = new Values\CallbackDataValues(Enums\Callback::SendPost, 'yes');
        $noData = new Values\CallbackDataValues(Enums\Callback::SendPost, 'no');
        $yesButton = $this->buildDataButton('Да', json_encode($yesData));
        $noButton = $this->buildDataButton('Нет', json_encode($noData));
        $checkData = new Values\CallbackDataValues(Enums\Callback::CheckPost, '');
        $checkButton = $this->buildDataButton('Проверить', json_encode($checkData));
        $keyboard = $this->buildKeyboard([$yesButton, $checkButton, $noButton]);
        return $keyboard;
    }
}
