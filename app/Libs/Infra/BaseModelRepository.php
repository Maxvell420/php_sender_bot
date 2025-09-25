<?php

namespace App\Libs\Infra;

use Illuminate\Database\Eloquent\Model;

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
}