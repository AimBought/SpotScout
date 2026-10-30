<?php

declare(strict_types=1);

namespace App\DTO;

final class SpotInputDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $status,
    ) {
    }
}
