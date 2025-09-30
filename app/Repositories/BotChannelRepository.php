<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\BotChannel;

class BotChannelRepository extends BaseModelRepository {

    protected string $class = BotChannel::class;

    public function findByChannelId(int $channel_id): ?BotChannel {
        return $this->model->where('channel_id', '=', $channel_id)->first();
    }

    public function findByTgId(int $tg_id): ?BotChannel {
        return $this->model->where('tg_id', '=', $tg_id)->first();
    }
}

