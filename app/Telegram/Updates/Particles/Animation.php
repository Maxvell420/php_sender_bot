<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;

class Animation extends Data {

    public function __construct(
        public string $file_name,
        public string $mime_type,
        public int $duration,
        public File $thumbnail,
        public File $thumb,
        public string $file_id
    ) {}
}
