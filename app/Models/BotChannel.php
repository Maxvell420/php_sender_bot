<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $channel_id
 * @property string $channel_link
 * @property int $status
 */

class BotChannel extends Model {

    protected $table = 'bot_channels';
    public $timestamps = false;

    protected $fillable = [
        'channel_id',
        'channel_link',
        'status'
    ];

    public function findByChatId(int $channel_id): ?BotChannel {
        return $this->where('channel_id', '=', $channel_id)->first();
    }
}
