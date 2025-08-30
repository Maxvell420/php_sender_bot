<?php

namespace App\Http\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ValidationException extends Exception
{

    public function render(): JsonResponse
    {
        // Возвращаем ТОЛЬКО то, что нужно, без stack trace
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
