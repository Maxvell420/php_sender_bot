<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseModelRepository {

    protected string $class = User::class;

    public function findByTgId(int $tg_id): ?User {
        return $this->model->where('tg_id', '=', $tg_id)->first();
    }

    public function listActiveUsers(): Collection {
        return $this->model->where('kicked', '=', 'no')->get();
    }
}

