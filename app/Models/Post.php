<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $update_id
 * @property string $content
 * @property int $actor_id
 */

class Post extends Model {

    protected $table = 'posts';

    protected $fillable = [
        'content',
        'actor_id',
        'created_at',
        'updated_at',
    ];

    public function getStartText(): string {
        $post = $this->where('id', '=', 1)->first();
        return $post->content;
    }
}
