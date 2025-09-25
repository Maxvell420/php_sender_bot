<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\JobUser;

class JobUserRepository extends BaseModelRepository {

    protected string $class = JobUser::class;
}

