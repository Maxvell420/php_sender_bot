<?php

namespace App\Telegram\UseCases;

use Throwable;
use App\Http\Exceptions\ValidationException;

abstract class UpdateHandler
{

    protected string $class;

    protected function buildVO(array $data)
    {

        return $this->class::from($data);
        // try {
        //     return $this->class::from($data);
        // } catch (Throwable) {
        //     throw new ValidationException('WRONG_DATA', 422);
        // }
    }
}
