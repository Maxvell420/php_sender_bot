<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
/**
 * @property string $completed
 * @property int $actor_id
 * @property string $job_id
 */

class JobUser extends Model {

    protected $table = 'jobs_users';

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'job_id',
        'completed',
    ];

    public function listByJob(int $job_id): Collection {
        return $this->where('job_id', '=', $job_id)->get();
    }

    public function complete(): void {
        $this->completed = 'yes';
    }

    public function isCompleted(): bool {
        return $this->completed == 'yes';
    }
}
