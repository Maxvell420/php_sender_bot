<?php

namespace App\Libs\Infra;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModelRepository {

    protected string $class = Model::class;

    public function __construct(protected Model $model) {}
}