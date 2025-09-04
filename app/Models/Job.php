<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $json
 * @property string $completed
 * @property int $actor_id
 * @property int $job_type
 * @property string $created_at
 * @property string $updated_at
 */

class Job extends Model {

    protected $table = 'tg_jobs';

    protected $fillable = [
        'actor_id',
        'json',
        'completed',
        'job_type',
        'created_at',
        'updated_at',
    ];

    public function complete(): void {
        $this->completed = 'yes';
    }

    public function findFirstNotCompleted(): ?Job {
        return $this->where('completed', '=', 'no')->orderBy('ID')->first();
    }
}
