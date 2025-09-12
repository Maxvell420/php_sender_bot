<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
