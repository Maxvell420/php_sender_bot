<?php

namespace App\Libs\Infra;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseModelRepository {

    protected Model $model;
    protected string $class = Model::class;

    public function __construct(Context $context) {
        $this->model = $context->getModel($this->class);
    }

    // TODO: Возвращать обьект чтобы удобно было писать тесты
    public function persist(Model $model): void {
        $model->save();
    }

    public function all(): Collection {
        return $this->model->all();
    }
}