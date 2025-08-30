<?php

namespace App\ValData;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

class Test extends Data
{
    public function __construct(
        #[Validation\Required, Validation\StringType, Validation\Max(255)]
        public string $name,
    ) {}
}
