<?php

namespace App\Telegram\UseCases;

use Throwable;
use App\Http\Exceptions\ValidationException;
use App\Telegram\Updates\Update as UpdateInterface;
use App\Models\Update;
abstract class UpdateHandler {

    protected string $class;

    protected function buildVO(array $data) {
        try {
            return $this->class::from($data);
        } catch (Throwable) {
            throw new ValidationException('WRONG_DATA', 422);
        }
    }

    abstract public function handleUpdate(array $data): UpdateInterface;
}
