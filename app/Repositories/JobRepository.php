<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\Job;

class JobRepository extends BaseModelRepository {

    protected string $class = Job::class;

    public function findFirstNotCompleted(): ?Job {
        return $this->model->where('completed', '=', 'no')->orderBy('ID')->first();
    }
}

