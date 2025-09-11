<?php

namespace App\Telegram;

use App\Libs\Telegram\TelegramRequest;
use App\Models\ {
    Update,
    Job
};
use App\Telegram\UseCases\Updates;
use Exception;
use App\Telegram\UseCases\JobsHandler;

class Demon {

    public function run(): void {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);

        $useCase = new Updates($telegram);

        while( true ) {
            $update = new Update();
            $update_id = $update->getNextUpdateId();
            print('ID обновления: ' . $update_id . "\n");
            $job = new Job();
            $job = $job->findFirstNotCompleted();

            if( $job ) {
                print("Выполняю работу $job->id" . "\n");
                $useCase->handleJob($job);
                print("Выполнил работу $job->id" . "\n");
            }
            else {
                print('работ нет' . "\n");
            }

            $updates = $useCase->getUpdates($update_id, 10);

            if( empty($updates['result']) ) {
                print('обновлений нет' . "\n");
                continue;
            }

            print('обновления есть' . "\n");

            foreach($updates['result'] as $update) {
                $useCase->handleUpdate($update);
            }
        }
    }
}
