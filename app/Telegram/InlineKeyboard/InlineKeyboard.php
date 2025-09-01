<?php

namespace App\Telegram\InlineKeyboard;

class InlineKeyboard {

    /**
        * @var InlineButton[]
    */

    public array $inline_keyboard;

    public function buildKeyboardData(): array {
        $data  = [];

        foreach($this->inline_keyboard as $button) {
            foreach($button as $property) {
                dd($property);

                if( $property ) {
                    
                }
            }
        }
    }
}