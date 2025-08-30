<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;

try {
    Route::post('/test', [BotController::class, 'testUpdates'])->name('testUpdates');
    Route::get('/getUpdates', [BotController::class, 'getUpdates'])->name('getUpdates');
} catch (Throwable) {
    return response()->json([
        'errors' => $e->getMessage()
    ], $e->getCode());
}
