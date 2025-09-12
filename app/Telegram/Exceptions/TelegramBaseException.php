<?php

namespace App\Telegram\Exceptions;

use Exception;
// use Illuminate\Http\JsonResponse;

class TelegramBaseException extends Exception {

    // public function render(): JsonResponse {
    //     return response()->json(
    //         [
    //             'status' => 'error',
    //             'message' => $this->getMessage(),
    //         ],
    //         $this->getCode()
    //     );
    // }
}
