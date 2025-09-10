<?php

namespace App\Http\Controllers;

use App\Libs\Telegram\TelegramRequest;
use Illuminate\Http\ {
    Request
};
use App\Telegram\UseCases\Updates;
use App\Models\Update;

class BotController extends Controller {

    public function getUpdates() {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);
        $update = new Update();
        $update_id = $update->getNextUpdateId();
        // 196800180
        $response = $telegram->getUpdates(196800500 + 10);
        dd(json_encode($response));
    }

    public function testUpdates(Request $request) {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);
        $useCase = new Updates($telegram);

        foreach($request->all('result')['result'] as $update) {
            $useCase->handleUpdate($update);
        }
    }
}
