<?php

declare(strict_types=1);

namespace App\Model;

final class User
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
        private ?string $avatarPath = null,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getAvatarPath(): ?string { return $this->avatarPath; }
    public function setAvatarPath(?string $avatarPath): void { $this->avatarPath = $avatarPath; }
}
