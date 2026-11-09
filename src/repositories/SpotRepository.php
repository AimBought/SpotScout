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
            'SELECT s.id, s.title, s.description, s.status, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, s.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.id'
        );
        $statement->execute();

        return array_map(fn (array $row): Spot => $this->mapRowToSpot($row), $statement->fetchAll());
    }

    public function find(int $id): ?Spot
    {
        $statement = $this->database->prepare(
            'SELECT s.id, s.title, s.description, s.status, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, s.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->mapRowToSpot($row);
    }

    public function findFeaturedSpot(): ?Spot
    {
        $statement = $this->database->query(
            'SELECT s.id, s.title, s.description, s.status, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, s.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC, s.id DESC LIMIT 1'
        );
        $row = $statement->fetch();

        return $row === false ? null : $this->mapRowToSpot($row);
    }

    /** @return array<int, Spot> */
    public function findByUserId(int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT s.id, s.title, s.description, s.status, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, s.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id WHERE s.user_id = :user_id ORDER BY s.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(fn (array $row): Spot => $this->mapRowToSpot($row), $statement->fetchAll());
    }

    /** @return array<int, Spot> */
    public function findBookmarkedByUserId(int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT s.id, s.title, s.description, s.status, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, s.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path '
            . 'FROM spot_bookmarks b JOIN spots s ON s.id = b.spot_id LEFT JOIN users u ON u.id = s.user_id '
            . 'WHERE b.user_id = :user_id ORDER BY b.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(fn (array $row): Spot => $this->mapRowToSpot($row), $statement->fetchAll());
    }

    /** @return array<int, int> */
    public function findBookmarkedIdsByUserId(int $userId): array
    {
        $statement = $this->database->prepare('SELECT spot_id FROM spot_bookmarks WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public function isBookmarkedByUser(int $userId, int $spotId): bool
    {
        $statement = $this->database->prepare('SELECT 1 FROM spot_bookmarks WHERE user_id = :user_id AND spot_id = :spot_id');
        $statement->execute(['user_id' => $userId, 'spot_id' => $spotId]);

        return $statement->fetchColumn() !== false;
    }

    /** Toggles a bookmark and returns its new state. */
    public function toggleBookmark(int $userId, int $spotId): bool
    {
        $delete = $this->database->prepare('DELETE FROM spot_bookmarks WHERE user_id = :user_id AND spot_id = :spot_id');
        $delete->execute(['user_id' => $userId, 'spot_id' => $spotId]);
        if ($delete->rowCount() > 0) {
            return false;
        }

        $insert = $this->database->prepare('INSERT INTO spot_bookmarks (user_id, spot_id) VALUES (:user_id, :spot_id) ON CONFLICT (user_id, spot_id) DO NOTHING');
        $insert->execute(['user_id' => $userId, 'spot_id' => $spotId]);

        return true;
    }

    public function save(SpotInputDTO $dto): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO spots (title, description, status, latitude, longitude, category, image_path, user_id) VALUES (:title, :description, :status, :latitude, :longitude, :category, :image_path, :user_id) RETURNING id'
        );
        $statement->execute([
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => $dto->status,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
            'category' => $dto->category,
            'image_path' => $dto->image_path,
            'user_id' => $dto->user_id,
        ]);

        return (int) $statement->fetchColumn();
    }

    /** @param array<int, string> $imagePaths */
    public function saveAdditionalImages(int $spotId, array $imagePaths): void
    {
        $statement = $this->database->prepare('INSERT INTO spot_images (spot_id, image_path) VALUES (:spot_id, :image_path)');
        foreach ($imagePaths as $imagePath) {
            $statement->execute(['spot_id' => $spotId, 'image_path' => $imagePath]);
        }
    }

    /** @return array<int, string> */
    public function findImagePaths(int $spotId, ?string $coverPath): array
    {
        $paths = $coverPath === null ? [] : [$coverPath];
        $statement = $this->database->prepare('SELECT image_path FROM spot_images WHERE spot_id = :spot_id ORDER BY id');
        $statement->execute(['spot_id' => $spotId]);
        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $path) {
            $paths[] = (string) $path;
        }
        return $paths;
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

    public function deleteOwnedByUser(int $spotId, int $userId): bool
    {
        $statement = $this->database->prepare('DELETE FROM spots WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $spotId, 'user_id' => $userId]);

        return $statement->rowCount() === 1;
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
        $spot->setLatitude($row['latitude'] === null ? null : (float) $row['latitude']);
        $spot->setLongitude($row['longitude'] === null ? null : (float) $row['longitude']);
        $spot->setCategory($row['category'] === null ? null : (string) $row['category']);
        $spot->setImagePath($row['image_path'] === null ? null : (string) $row['image_path']);
        $spot->setUserId($row['user_id'] === null ? null : (int) $row['user_id']);
        $spot->setAuthorName($row['author_name'] === null ? null : (string) $row['author_name']);
        $spot->setAuthorAvatarPath($row['author_avatar_path'] === null ? null : (string) $row['author_avatar_path']);

        return $spot;
    }
}
