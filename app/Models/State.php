<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $title
 * @property int $actor_id
 * @property int $state_id
 * @property string $json
 */

class State extends Model
{

    protected $table = 'states';

    protected $fillable = [
        'actor_id',
        'state_id',
        'json'
    ];

    public function findByUser(int $user_id): ?State
    {
        return $this->where('actor_id', '=', $user_id)->first();
    }
}
