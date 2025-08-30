<?php

namespace App\Http\Controllers;

use App\ValData\Test;

class BotController extends Controller
{

    public function helloWorld(Test $data)
    {
        dd($data);
        echo 1;
    }
}
