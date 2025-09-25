<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\BotChannel;

class BotChannelRepository extends BaseModelRepository {

    protected string $class = BotChannel::class;
}

