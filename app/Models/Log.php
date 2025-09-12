<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
/**
 * @property string $info
 * @property string $created_at
 */

class Log extends Model {

    protected $table = 'logs';

    protected $fillable = [
        'info',
        'created_at'
    ];

    public function listLast(int $limit = 20): Collection {
        return $this->latest('id')->limit($limit)->get();
    }
}
