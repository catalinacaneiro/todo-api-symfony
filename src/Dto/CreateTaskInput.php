<?php

namespace App\Dto;

final readonly class CreateTaskInput
{
    public function __construct(
        public string $title = '',
        public ?string $description = null,
    ) {
    }
}
