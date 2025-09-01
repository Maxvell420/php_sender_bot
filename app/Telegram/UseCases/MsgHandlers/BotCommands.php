<?php

namespace App\Telegram\UseCases\MsgHandlers;

use App\Telegram\Updates\MessageUpdate;
use App\Models\User;
use App\Telegram\Updates\Update;
use App\Telegram\Enums;

class BotCommands {

    public function handleUpdate(MessageUpdate $data): Update {
        $user_id = $data->findMessageFromId();
        $user = new User()->findByTgId($user_id);

        if( !$user ) {
            $user = $this->createNewUser($data->getUserName(), $user_id, true);
            // Вообще такого быть не должно
        }

        // если это команда то в ней есть текст
        $text = $data->findText();

        foreach(Enums\Commands::cases() as $case) {
            if( str_contains($text, $case->value) ) {
                $this->handleCommand($case, $data);
            }
        }

        return $data;
    }

    private function handleCommand(Enums\Commands $case, MessageUpdate $data): void {
        match($case) {
            Enums\Commands::Start => $this->handleStart($data),
            default => ''
        };
    }

    private function handleStart(MessageUpdate $data): void {
        
    }

    private function handleUndefined(MessageUpdate $data): void {}

    private function createNewUser(string $user_name, int $tg_id, bool $member): User {
        $user = new User();
        $user->user_name = $user_name;
        $user->tg_id = $tg_id;
        $member == true ? $user->setMember() : $user->setKicked();
        $user->save();
        return $user;
    }
}
