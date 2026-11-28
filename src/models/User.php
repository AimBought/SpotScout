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
        private string $role = 'user',
        private ?string $bio = null,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getAvatarPath(): ?string { return $this->avatarPath; }
    public function getRole(): string { return $this->role; }
    public function getBio(): ?string { return $this->bio; }
    public function setAvatarPath(?string $avatarPath): void { $this->avatarPath = $avatarPath; }
    public function setBio(?string $bio): void { $this->bio = $bio; }
}
