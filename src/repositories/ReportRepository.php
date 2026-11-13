<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Report;
use DateTimeImmutable;
use PDO;

final class ReportRepository
{
    public function __construct(private PDO $database)
    {
    }

    /** @param array<int, string> $proposedPhotoKeys */
    public function create(int $spotId, int $reporterId, string $type, string $description, ?float $proposedLatitude = null, ?float $proposedLongitude = null, array $proposedPhotoKeys = []): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO reports (spot_id, reporter_id, report_type, description, proposed_latitude, proposed_longitude, proposed_photo_keys) '
            . 'VALUES (:spot_id, :reporter_id, :type, :description, :proposed_latitude, :proposed_longitude, CAST(:proposed_photo_keys AS JSONB)) RETURNING id'
        );
        $statement->execute([
            'spot_id' => $spotId, 'reporter_id' => $reporterId, 'type' => $type, 'description' => $description,
            'proposed_latitude' => $proposedLatitude, 'proposed_longitude' => $proposedLongitude,
            'proposed_photo_keys' => json_encode($proposedPhotoKeys, JSON_THROW_ON_ERROR),
        ]);
        return (int) $statement->fetchColumn();
    }

    public function createEscalatedDelete(int $spotId, int $reporterId, string $description): int
    {
        $statement = $this->database->prepare(
            "INSERT INTO reports (spot_id, reporter_id, report_type, description, status) VALUES (:spot_id, :reporter_id, 'delete', :description, 'escalated') RETURNING id"
        );
        $statement->execute(['spot_id' => $spotId, 'reporter_id' => $reporterId, 'description' => $description]);
        return (int) $statement->fetchColumn();
    }

    /** @return array<int, Report> */
    public function findPending(): array { return $this->findByStatus('pending'); }

    /** @return array<int, Report> */
    public function findEscalated(): array { return $this->findByStatus('escalated'); }

    public function countOpen(): int
    {
        $statement = $this->database->query("SELECT COUNT(*) FROM reports WHERE status IN ('pending', 'escalated')");
        return (int) $statement->fetchColumn();
    }

    public function find(int $id): ?Report
    {
        $rows = $this->findByStatus(null, $id);
        return $rows[0] ?? null;
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending', 'escalated', 'resolved', 'rejected'], true)) {
            throw new \InvalidArgumentException('Invalid report status.');
        }
        $statement = $this->database->prepare('UPDATE reports SET status = :status WHERE id = :id');
        $statement->execute(['id' => $id, 'status' => $status]);
        return $statement->rowCount() === 1;
    }

    /** @return array<int, Report> */
    private function findByStatus(?string $status, ?int $id = null): array
    {
        $sql = 'SELECT r.id, r.spot_id, r.reporter_id, r.report_type, r.description, r.status, r.created_at,
                s.title AS spot_title, s.user_id AS owner_id, owner.name AS owner_name, reporter.name AS reporter_name,
                s.category, s.latitude, s.longitude, s.image_path, r.proposed_latitude, r.proposed_longitude, r.proposed_photo_keys
                FROM reports r JOIN spots s ON s.id = r.spot_id
                LEFT JOIN users owner ON owner.id = s.user_id LEFT JOIN users reporter ON reporter.id = r.reporter_id';
        $conditions = [];
        $params = [];
        if ($status !== null) { $conditions[] = 'r.status = :status'; $params['status'] = $status; }
        if ($id !== null) { $conditions[] = 'r.id = :id'; $params['id'] = $id; }
        if ($conditions !== []) { $sql .= ' WHERE ' . implode(' AND ', $conditions); }
        $sql .= ' ORDER BY r.created_at ASC, r.id ASC';
        $statement = $this->database->prepare($sql);
        $statement->execute($params);
        return array_map(static fn (array $row): Report => new Report(
            (int) $row['id'], (int) $row['spot_id'], (int) $row['reporter_id'], (string) $row['report_type'],
            (string) $row['description'], (string) $row['status'], new DateTimeImmutable((string) $row['created_at']),
            (string) $row['spot_title'], $row['owner_id'] === null ? null : (int) $row['owner_id'],
            $row['owner_name'] === null ? null : (string) $row['owner_name'],
            $row['reporter_name'] === null ? null : (string) $row['reporter_name'],
            $row['category'] === null ? null : (string) $row['category'],
            $row['latitude'] === null ? null : (float) $row['latitude'],
            $row['longitude'] === null ? null : (float) $row['longitude'],
            $row['image_path'] === null ? null : (string) $row['image_path'],
            $row['proposed_latitude'] === null ? null : (float) $row['proposed_latitude'],
            $row['proposed_longitude'] === null ? null : (float) $row['proposed_longitude'],
            array_values(array_filter(json_decode((string) $row['proposed_photo_keys'], true) ?: [], 'is_string')),
        ), $statement->fetchAll());
    }
}
