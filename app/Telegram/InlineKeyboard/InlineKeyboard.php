<?php

namespace App\Telegram\InlineKeyboard;

class InlineKeyboard
{

    /**
     * @var InlineButton[]
     */

    public array $buttons = [];

    public function addButton(InlineButton $button): void
    {
        $this->buttons[] = $button;
    }

    public function buildKeyboardData(): array
    {
        $keyboard = [];
        $buttons  = [];

        foreach ($this->buttons as $button) {
            foreach ($button as $property => $value) {

                if ($value) {
                    $buttons[$property] = $value;
                }
            }
        }

        if (!empty($buttons)) {
            $keyboard['inline_keyboard'] = [[$buttons]];
        }

        return $keyboard;
    }

    public function isEmpty(): bool
    {
        return empty($this->buttons);
    }
}
