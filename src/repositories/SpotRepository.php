<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\SpotInputDTO;
use App\Model\Spot;
use DateTimeImmutable;
use PDO;

final class SpotRepository
{
    public function __construct(private PDO $database)
    {
    }

    /** @return array<int, Spot> */
    public function findAll(): array
    {
        $statement = $this->database->prepare(
            'SELECT id, title, description, status, created_at, updated_at FROM spots ORDER BY id'
        );
        $statement->execute();

        return array_map(fn (array $row): Spot => $this->mapRowToSpot($row), $statement->fetchAll());
    }

    public function find(int $id): ?Spot
    {
        $statement = $this->database->prepare(
            'SELECT id, title, description, status, created_at, updated_at FROM spots WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->mapRowToSpot($row);
    }

    public function save(SpotInputDTO $dto): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO spots (title, description, status) VALUES (:title, :description, :status) RETURNING id'
        );
        $statement->execute([
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => $dto->status,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function update(Spot $spot): void
    {
        $statement = $this->database->prepare(
            'UPDATE spots SET title = :title, description = :description, status = :status, '
            . 'updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute([
            'id' => $spot->getId(),
            'title' => $spot->getTitle(),
            'description' => $spot->getDescription(),
            'status' => $spot->getStatus(),
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM spots WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /** @param array<string, mixed> $row */
    private function mapRowToSpot(array $row): Spot
    {
        $spot = new Spot();
        $spot->setId((int) $row['id']);
        $spot->setTitle((string) $row['title']);
        $spot->setDescription($row['description'] === null ? null : (string) $row['description']);
        $spot->setStatus((string) $row['status']);
        $spot->setCreatedAt(new DateTimeImmutable((string) $row['created_at']));
        $spot->setUpdatedAt(new DateTimeImmutable((string) $row['updated_at']));

        return $spot;
    }
}
