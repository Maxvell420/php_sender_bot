<?php

namespace App\Libs\Infra;

use App\Libs\Infra\Context;
use Error;
use Throwable;

// Родончальник демонов, он обрабатывает все ошибки которые прилетают с демонов которые демоны не обработали
abstract class InnerDemon {

    public function __construct(private Context $context) {}

    // Основная работа
    public abstract function run(): void;

    protected abstract function handleFallback(): void;

    protected function runJob(): void {
        try {
            $this->run();
        } catch (Throwable $e) {
            $this->handleFallback();
            // зайдем сюда если пришла какая-то фигня из тг из которой не смогли сконструировать обьект

        } catch (Error $e) {
            $this->handleFallback();
            // 26.09.2025 сейчас это может случится если в 'красивых сообщениях' что-то пошло не так
        }
    }
}