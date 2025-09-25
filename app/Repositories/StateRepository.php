<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\State;

class StateRepository extends BaseModelRepository {

    protected string $class = State::class;
}

