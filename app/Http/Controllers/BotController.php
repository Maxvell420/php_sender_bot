<?php

namespace App\Http\Controllers;

use App\ValData\Test;
use Illuminate\Http\{Request, Response};

class BotController extends Controller
{

    public function helloWorld(Request $request, Response $response)
    {
        $values  = $this->buildVO(Test::class, $request->all());
        return $values;
    }
}
