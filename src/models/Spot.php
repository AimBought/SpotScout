<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Spot
{
    private ?int $id = null;
    private string $title = '';
    private ?string $description = null;
    private string $status = 'active';
    private ?DateTimeImmutable $createdAt = null;
    private ?DateTimeImmutable $updatedAt = null;
    private ?float $latitude = null;
    private ?float $longitude = null;
    private ?string $category = null;
    private ?string $image_path = null;
    private ?int $userId = null;
    private ?string $authorName = null;
    private ?string $authorAvatarPath = null;

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $value): void { $this->latitude = $value; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $value): void { $this->longitude = $value; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $value): void { $this->category = $value; }
    public function getImagePath(): ?string { return $this->image_path; }
    public function setImagePath(?string $value): void { $this->image_path = $value; }
    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $value): void { $this->userId = $value; }
    public function getAuthorName(): ?string { return $this->authorName; }
    public function setAuthorName(?string $value): void { $this->authorName = $value; }
    public function getAuthorAvatarPath(): ?string { return $this->authorAvatarPath; }
    public function setAuthorAvatarPath(?string $value): void { $this->authorAvatarPath = $value; }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getShortDescription(): string
    {
        if ($this->description === null) {
            return '';
        }

        if (preg_match('/^.{101}/us', $this->description) === 1) {
            preg_match('/^.{0,100}/us', $this->description, $matches);

            return $matches[0] . '...';
        }

        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
