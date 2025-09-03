<?php

namespace App\Http\Controllers;

use App\Libs\Telegram\TelegramRequest;
use Illuminate\Http\ {
    Request
};
use App\Telegram\UseCases\Updates;
use App\Models\Update;
use App\Telegram\Demon;

class BotController extends Controller {

    public function getUpdates() {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);
        $update = new Update();
        $update_id = $update->getNextUpdateId();
        // 196800180
        $response = $telegram->getUpdates($update_id);
        dd(json_encode($response));
    }

    public function testUpdates(Request $request) {
        $useCase = new Updates();
        $useCase->handleUpdates($request->all('result')['result']);
    }
}
