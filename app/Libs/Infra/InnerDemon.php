<?php

namespace App\Libs\Infra;

use App\Libs\Infra\Context;
use Error;
use Exception;

// Родоначальник демонов, он обрабатывает все ошибки которые прилетают с демонов которые демоны не обработали
abstract class InnerDemon {

    public function __construct(protected Context $cntx) {}

    // Основная работа
    protected abstract function run(): void;

    protected abstract function handleError(string $message): void;
    protected abstract function handleException(string $message): void;
    final public function runJob(): void {
        while( true ) {
            try {
                $this->run();
            } catch (Exception $e) {
                $this->handleException($e->getMessage());

                // зайдем сюда если пришла какая-то фигня из тг из которой не смогли сконструировать обьект
                // или ошибка в ответе курла...

            } catch (Error $e) {
                $this->handleError($e->getMessage());
                // 26.09.2025 сейчас это может случится если в 'красивых сообщениях' что-то пошло не так
            }
        }
    }
}
