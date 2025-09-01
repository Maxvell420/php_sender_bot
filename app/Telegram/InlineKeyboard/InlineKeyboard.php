<?php

namespace App\Telegram\InlineKeyboard;

class InlineKeyboard {

    /**
        * @var InlineButton[]
    */

    public array $inline_keyboard = [];

    public function addButton(InlineButton $button): void {
        $this->inline_keyboard[] = $button;
    }

    public function buildKeyboardData(): array {
        $data  = [];

        foreach($this->inline_keyboard as $button) {
            foreach($button as $property => $value) {
                dd($property);

                if( $value ) {
                    
                }
            }
        }
    }
}