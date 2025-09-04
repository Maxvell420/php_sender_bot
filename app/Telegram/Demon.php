<?php

namespace App\Telegram;

use App\Libs\Telegram\TelegramRequest;
use App\Models\Update;
use App\Telegram\UseCases\Updates;
use Exception;

class Demon {

    public function run(): void {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);

        $useCase = new Updates($telegram);
        while( true ) {
            $update = new Update();
            $update_id = $update->getNextUpdateId();

            try {
                $updates = $telegram->getUpdates($update_id, 15);
                print('приняты обновления' . "\n");

                if( empty($updates['result']) ) {
                    // Тут сделать рассылку
                    print('обновлений нет' . "\n");
                    continue;
                }

                print('обновления есть' . "\n");

                foreach($updates['result'] as $update) {
                    $useCase->handleUpdate($update);
                }
            } catch (Exception $e) {
                $status = $e->getCode();

                if( str_starts_with(4, "$status") ) {
                    $useCase->handleErrorUpdate($update);
                }
                else {
                    // Как-то обрабатывать, сейчас просто спать час
                    sleep(env('WRONG_ANSWER'));
                }
            }
        }
    }
}
