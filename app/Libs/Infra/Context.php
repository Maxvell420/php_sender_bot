<?php

namespace App\Libs\Infra;

use App\Models\ {
    Job,
    BotChannel,
    JobUser,
    User,
    Update,
    State,
    Log,
    Post
};
use Illuminate\Database\Eloquent\Model;

class Context {

    public function getModel(string $model_table): Model {
        return $this->getModels()[$model_table];
    }

    public function getModels(): array {
        return [
            Job::class => new Job(),
            BotChannel::class => new BotChannel(),
            JobUser::class => new JobUser(),
            User::class => new User(),
            Update::class => new Update(),
            State::class => new State(),
            Log::class => new Log(),
            Post::class => new Post()
        ];
    }
}

