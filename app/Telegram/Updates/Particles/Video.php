<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;

class Video extends Data {

    public function __construct(
        public string $file_name,
        public string $mime_type,
        public string $file_id,
    ) {}
}
