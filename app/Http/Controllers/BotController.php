<?php

namespace App\Http\Controllers;

use App\Libs\Telegram\TelegramRequest;
use App\ValData\Test;
use Illuminate\Http\{Request};

class BotController extends Controller
{

    public function helloWorld(Request $request)
    {
        $values  = $this->buildVO(Test::class, $request->all());
        return $values;
    }

    public function getUpdates()
    {
        $secret = env('TG_BOT_SECRET');
        $telegram = new TelegramRequest($secret);
        $response = $telegram->getUpdates();
        dd($response);
    }
}
