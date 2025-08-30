<?php

namespace App\Http\Controllers;

use App\Libs\Telegram\TelegramRequest;
use Illuminate\Http\{Request};
use App\Telegram\UseCases\Updates;

class BotController extends Controller
{

    public function getUpdates()
    {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);
        $response = $telegram->getUpdates();
    }

    public function testUpdates(Request $request)
    {
        $useCase = new Updates();
        $useCase->handleUpdates($request->all('result')['result']);
    }
}
