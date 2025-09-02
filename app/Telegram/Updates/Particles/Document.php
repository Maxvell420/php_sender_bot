<?php

namespace App\Telegram\Updates\Particles;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class Document extends Data
{

    public function __construct(

        #[
            Validation\Required,
            Validation\StringType
        ]
        public string $file_id,
        public ?string $caption = null
    ) {}

    public function hasCaption(): bool
    {
        return (bool) $this->caption;
    }
}
