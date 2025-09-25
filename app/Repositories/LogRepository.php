<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\Log;

class LogRepository extends BaseModelRepository {

    protected string $class = Log::class;
}

