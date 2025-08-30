<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;

try {
    Route::post('/hello', [BotController::class, 'helloWorld'])->name('hello_worlds');
} catch (Throwable) {
    return 1;
    return response()->json([
        'errors' => $e->getMessage()
    ], $e->getCode());
}
