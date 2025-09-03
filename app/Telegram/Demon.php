<?php

namespace App\Telegram;

use App\Libs\Telegram\TelegramRequest;
use App\Models\Update;
use App\Telegram\UseCases\Updates;

class Demon {

    public function run(): void {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);

        while( true ) {
            $update = new Update();
            $update_id = $update->getNextUpdateId();
            $updates = $telegram->getUpdates($update_id, 20);

            if( empty($updates['result']) ) {
                sleep(2);
            }

            $useCase = new Updates();
            $useCase->handleUpdates($updates);
        }
    }
}
