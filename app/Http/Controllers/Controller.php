<?php

namespace App\Http\Controllers;

use Throwable;
use App\Http\Exceptions\ValidationException;

abstract class Controller
{
    protected function buildVO(string $class, array $data)
    {

        try {
            return $class::from($data);
        } catch (Throwable) {
            throw new ValidationException('WRONG_DATA', 422);
        }
    }
}
