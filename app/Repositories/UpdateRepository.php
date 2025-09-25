<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\Update;

class UpdateRepository extends BaseModelRepository {

    protected string $class = Update::class;
}

