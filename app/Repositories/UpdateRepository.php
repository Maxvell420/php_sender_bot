<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\Update;

class UpdateRepository extends BaseModelRepository {

    protected string $class = Update::class;

    public function getNextUpdateId(): int {
        $update = $this->model->max('update_id');
        return ($update ? $update : 1) + 1;
    }
}

