<?php

namespace App\Repositories;

use App\Libs\Infra\BaseModelRepository;
use App\Models\Post;

class PostRepository extends BaseModelRepository {

    protected string $class = Post::class;
}

