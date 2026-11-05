<?php

declare(strict_types=1);

namespace App\DTO;

final class SpotInputDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $status,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $category = null,
        public ?string $image_path = null,
        public ?int $user_id = null,
    ) {
    }
}
