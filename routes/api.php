<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;

try {
    Route::post('/hello', [BotController::class, 'helloWorld'])->name('hello_worlds');
    Route::get('/getUpdates', [BotController::class, 'getUpdates'])->name('getUpdates');
} catch (Throwable) {
    return response()->json([
        'errors' => $e->getMessage()
    ], $e->getCode());
}
