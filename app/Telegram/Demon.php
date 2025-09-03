<?php

namespace App\Telegram;

use App\Libs\Telegram\TelegramRequest;
use App\Models\Update;
use App\Telegram\UseCases\Updates;

class Demon {

    public function run(): void {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);

        $useCase = new Updates();
        while( true ) {
            $update = new Update();
            $update_id = $update->getNextUpdateId();
            $updates = $telegram->getUpdates($update_id, 5);
            print('приняты обновления' . "\n");

            if( empty($updates['result']) ) {
                print('обновлений нет' . "\n");
                continue;
            }

            print('обновления есть' . "\n");
            $useCase->handleUpdates($updates['result']);
        }
    }
}
