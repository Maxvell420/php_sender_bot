<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\JobUser;
use Illuminate\Database\Eloquent\Collection;

class JobUserRepository extends BaseModelRepository {

    protected string $class = JobUser::class;

    public function listByJob(int $job_id): Collection {
        return $this->model->where('job_id', '=', $job_id)->get();
    }
}

