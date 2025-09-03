<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property int $tg_id
 * @property string $user_name
 * @property string $kicked
 * @property string $is_admin
 */

class User extends Authenticatable {

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tg_id',
        'user_name',
        'kicked',
        'created_at',
        'updated_at',
        'is_admin'
    ];

    public function findByTgId(int $tg_id): ?User {
        return $this->where('tg_id', '=', $tg_id)->first();
    }

    public function isMember(): bool {
        return $this->kicked == 'no';
    }

    public function setKicked(): void {
        $this->kicked = 'yes';
    }

    public function setMember(): void {
        $this->kicked = 'no';
    }

    public function isAdmin(): bool {
        return $this->is_admin == 'yes';
    }

    public function listActiveUsers(): Collection {
        return $this->where('kicked', '=', 'no')->get();
    }
}
