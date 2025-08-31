<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $update_id
 */

class Update extends Model
{

    protected $table = 'updates';

    protected $fillable = [
        'update_id',
    ];

    public function getNextUpdateId(): int
    {
        $update = $this->max('update_id');
        return ($update ? $update : 1) + 1;
    }
}
