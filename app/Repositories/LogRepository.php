<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\Log;
use Illuminate\Database\Eloquent\Collection;

class LogRepository extends BaseModelRepository {

    protected string $class = Log::class;

    public function listLast(int $limit = 60): Collection {
        return $this->model->latest('id')->limit($limit)->get();
    }
}

