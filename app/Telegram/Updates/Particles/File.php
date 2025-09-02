<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class File extends Data
{

    public function __construct(

        #[
            Validation\Required,
            Validation\StringType
        ]
        public string $file_id,
    ) {}

    public function getFileId(): int
    {
        return $this->file_id;
    }
}
