<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\State;

class StateRepository extends BaseModelRepository {

    protected string $class = State::class;

    public function findByUser(int $user_id): ?State {
        return $this->model->where('actor_id', '=', $user_id)->first();
    }
}

