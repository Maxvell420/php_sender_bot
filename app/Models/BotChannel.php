<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $channel_id
 * @property string $channel_link
 * @property int $status
 * @property int $tg_id
 */

class BotChannel extends Model {

    protected $table = 'bot_channels';
    public $timestamps = false;

    protected $fillable = [
        'channel_id',
        'channel_link',
        'status',
        'tg_id'
    ];

    public function findByChannelId(int $channel_id): ?BotChannel {
        return $this->where('channel_id', '=', $channel_id)->first();
    }

    public function findByTgId(int $tg_id): ?BotChannel {
        return $this->where('tg_id', '=', $tg_id)->first();
    }
}
