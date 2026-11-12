<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Report
{
    public function __construct(
        private int $id,
        private int $spotId,
        private int $reporterId,
        private string $reportType,
        private string $description,
        private string $status,
        private DateTimeImmutable $createdAt,
        private string $spotTitle,
        private ?int $ownerId,
        private ?string $ownerName,
        private ?string $reporterName,
        private ?string $category,
        private ?float $latitude,
        private ?float $longitude,
        private ?string $imagePath,
        private ?float $proposedLatitude,
        private ?float $proposedLongitude,
        /** @var array<int, string> */
        private array $proposedPhotoKeys,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getSpotId(): int { return $this->spotId; }
    public function getReporterId(): int { return $this->reporterId; }
    public function getReportType(): string { return $this->reportType; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getSpotTitle(): string { return $this->spotTitle; }
    public function getOwnerId(): ?int { return $this->ownerId; }
    public function getOwnerName(): ?string { return $this->ownerName; }
    public function getReporterName(): ?string { return $this->reporterName; }
    public function getCategory(): ?string { return $this->category; }
    public function getLatitude(): ?float { return $this->latitude; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function getImagePath(): ?string { return $this->imagePath; }
    public function getProposedLatitude(): ?float { return $this->proposedLatitude; }
    public function getProposedLongitude(): ?float { return $this->proposedLongitude; }
    /** @return array<int, string> */
    public function getProposedPhotoKeys(): array { return $this->proposedPhotoKeys; }
}
