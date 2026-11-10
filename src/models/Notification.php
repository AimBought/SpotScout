<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Notification
{
    public function __construct(
        private int $id,
        private int $userId,
        private string $message,
        private bool $isRead,
        private DateTimeImmutable $createdAt,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getMessage(): string { return $this->message; }
    public function isRead(): bool { return $this->isRead; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}
