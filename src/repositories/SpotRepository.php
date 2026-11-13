<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\SpotInputDTO;
use App\Model\Spot;
use DateTimeImmutable;
use DomainException;
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
            'SELECT s.id, s.title, s.description, s.status, s.rejection_reason, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, '
            . 'COALESCE(s.image_path, (SELECT si.image_path FROM spot_images si WHERE si.spot_id = s.id AND si.status = \'approved\' ORDER BY si.created_at ASC, si.id ASC LIMIT 1)) AS preview_image_path, '
            . 's.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id WHERE s.status = \'approved\' ORDER BY s.id'
        );
        $statement->execute();

        return array_map(fn (array $row): Spot => $this->mapRowToSpot($row), $statement->fetchAll());
    }

    public function find(int $id): ?Spot
    {
        $statement = $this->database->prepare(
            'SELECT s.id, s.title, s.description, s.status, s.rejection_reason, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, '
            . 'COALESCE(s.image_path, (SELECT si.image_path FROM spot_images si WHERE si.spot_id = s.id AND si.status = \'approved\' ORDER BY si.created_at ASC, si.id ASC LIMIT 1)) AS preview_image_path, '
            . 's.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->mapRowToSpot($row);
    }

    public function findFeaturedSpot(): ?Spot
    {
        $statement = $this->database->query(
            'SELECT s.id, s.title, s.description, s.status, s.rejection_reason, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, '
            . 'COALESCE(s.image_path, (SELECT si.image_path FROM spot_images si WHERE si.spot_id = s.id AND si.status = \'approved\' ORDER BY si.created_at ASC, si.id ASC LIMIT 1)) AS preview_image_path, '
            . 's.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id WHERE s.status = \'approved\' ORDER BY s.created_at DESC, s.id DESC LIMIT 1'
        );
        $row = $statement->fetch();

        return $row === false ? null : $this->mapRowToSpot($row);
    }

    /** @return array<int, Spot> */
    public function findByUserId(int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT s.id, s.title, s.description, s.status, s.rejection_reason, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, '
            . 'COALESCE(s.image_path, (SELECT si.image_path FROM spot_images si WHERE si.spot_id = s.id AND si.status = \'approved\' ORDER BY si.created_at ASC, si.id ASC LIMIT 1)) AS preview_image_path, '
            . 's.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id WHERE s.user_id = :user_id ORDER BY s.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(fn (array $row): Spot => $this->mapRowToSpot($row), $statement->fetchAll());
    }

    /** @return array<int, Spot> */
    public function findBookmarkedByUserId(int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT s.id, s.title, s.description, s.status, s.rejection_reason, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, '
            . 'COALESCE(s.image_path, (SELECT si.image_path FROM spot_images si WHERE si.spot_id = s.id AND si.status = \'approved\' ORDER BY si.created_at ASC, si.id ASC LIMIT 1)) AS preview_image_path, '
            . 's.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path '
            . 'FROM spot_bookmarks b JOIN spots s ON s.id = b.spot_id LEFT JOIN users u ON u.id = s.user_id '
            . 'WHERE b.user_id = :user_id AND s.status = \'approved\' ORDER BY b.created_at DESC'
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

    /** @return array<int, Spot> */
    public function findPendingSpots(?int $excludeUserId = null): array
    {
        $query = 'SELECT s.id, s.title, s.description, s.status, s.rejection_reason, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, s.user_id, u.name AS author_name, u.avatar_path AS author_avatar_path FROM spots s LEFT JOIN users u ON u.id = s.user_id WHERE s.status = \'pending\'';
        $parameters = [];
        if ($excludeUserId !== null) {
            $query .= ' AND (s.user_id IS NULL OR s.user_id <> :exclude_user_id)';
            $parameters['exclude_user_id'] = $excludeUserId;
        }
        $query .= ' ORDER BY s.created_at ASC';
        $statement = $this->database->prepare($query);
        $statement->execute($parameters);

        return array_map(fn (array $row): Spot => $this->mapRowToSpot($row), $statement->fetchAll());
    }

    /** @return array<int, array{id:int, spot_id:int, image_path:string, spot_title:string, contributor_user_id:?int, contributor_name:?string}> */
    public function findPendingImageContributions(?int $excludeUserId = null): array
    {
        $query = 'SELECT si.id, si.spot_id, si.image_path, s.title AS spot_title, si.contributor_user_id, u.name AS contributor_name '
            . 'FROM spot_images si JOIN spots s ON s.id = si.spot_id '
            . 'LEFT JOIN users u ON u.id = si.contributor_user_id WHERE si.status = \'pending\' AND s.status = \'approved\'';
        $parameters = [];
        if ($excludeUserId !== null) {
            $query .= ' AND (si.contributor_user_id IS NULL OR si.contributor_user_id <> :exclude_user_id)';
            $parameters['exclude_user_id'] = $excludeUserId;
        }
        $query .= ' ORDER BY si.created_at ASC, si.id ASC';
        $statement = $this->database->prepare($query);
        $statement->execute($parameters);

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'spot_id' => (int) $row['spot_id'],
            'image_path' => (string) $row['image_path'],
            'spot_title' => (string) $row['spot_title'],
            'contributor_user_id' => $row['contributor_user_id'] === null ? null : (int) $row['contributor_user_id'],
            'contributor_name' => $row['contributor_name'] === null ? null : (string) $row['contributor_name'],
        ], $statement->fetchAll());
    }

    /** @return array<int, array{spot:Spot, images:array<int, array{id:int, image_path:string, contributor_user_id:?int, contributor_name:?string}>}> */
    public function findPendingImageContributionGroups(?int $excludeUserId = null): array
    {
        $query = 'SELECT s.id, s.title, s.description, s.status, s.rejection_reason, s.created_at, s.updated_at, s.latitude, s.longitude, s.category, s.image_path, s.user_id, '
            . 'u.name AS author_name, u.avatar_path AS author_avatar_path, si.id AS contribution_image_id, si.image_path AS contribution_image_path, '
            . 'si.contributor_user_id, contributor.name AS contributor_name '
            . 'FROM spots s JOIN spot_images si ON si.spot_id = s.id LEFT JOIN users u ON u.id = s.user_id '
            . 'LEFT JOIN users contributor ON contributor.id = si.contributor_user_id '
            . 'WHERE si.status = \'pending\' AND s.status = \'approved\'';
        $parameters = [];
        if ($excludeUserId !== null) {
            $query .= ' AND (si.contributor_user_id IS NULL OR si.contributor_user_id <> :exclude_user_id)';
            $parameters['exclude_user_id'] = $excludeUserId;
        }
        $query .= ' ORDER BY s.created_at ASC, s.id ASC, si.created_at ASC, si.id ASC';
        $statement = $this->database->prepare($query);
        $statement->execute($parameters);

        $groups = [];
        foreach ($statement->fetchAll() as $row) {
            $spotId = (int) $row['id'];
            if (!isset($groups[$spotId])) {
                $groups[$spotId] = ['spot' => $this->mapRowToSpot($row), 'images' => []];
            }
            $groups[$spotId]['images'][] = [
                'id' => (int) $row['contribution_image_id'],
                'image_path' => (string) $row['contribution_image_path'],
                'contributor_user_id' => $row['contributor_user_id'] === null ? null : (int) $row['contributor_user_id'],
                'contributor_name' => $row['contributor_name'] === null ? null : (string) $row['contributor_name'],
            ];
        }

        return array_values($groups);
    }

    /** @return array{id:int, spot_id:int, image_path:string, spot_title:string, contributor_user_id:?int, contributor_name:?string}|null */
    public function findPendingImageContribution(int $imageId): ?array
    {
        $statement = $this->database->prepare(
            'SELECT si.id, si.spot_id, si.image_path, s.title AS spot_title, si.contributor_user_id, u.name AS contributor_name '
            . 'FROM spot_images si JOIN spots s ON s.id = si.spot_id '
            . 'LEFT JOIN users u ON u.id = si.contributor_user_id '
            . 'WHERE si.id = :id AND si.status = \'pending\' AND s.status = \'approved\''
        );
        $statement->execute(['id' => $imageId]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'spot_id' => (int) $row['spot_id'],
            'image_path' => (string) $row['image_path'],
            'spot_title' => (string) $row['spot_title'],
            'contributor_user_id' => $row['contributor_user_id'] === null ? null : (int) $row['contributor_user_id'],
            'contributor_name' => $row['contributor_name'] === null ? null : (string) $row['contributor_name'],
        ];
    }

    /** @param array<int, string> $imagePaths @return array<int, int> */
    public function addImageContribution(int $spotId, int $userId, array $imagePaths, string $status): array
    {
        $this->database->beginTransaction();
        try {
            $lock = $this->database->prepare("SELECT id FROM spots WHERE id = :spot_id AND status = 'approved' FOR UPDATE");
            $lock->execute(['spot_id' => $spotId]);
            if ($lock->fetchColumn() === false) {
                throw new DomainException('Nie można już dodawać zdjęć do tego spotu.');
            }

            $count = $this->database->prepare(
                'SELECT (SELECT COUNT(*) FROM spot_images WHERE spot_id = :images_spot_id AND contributor_user_id = :contributor_id) '
                . '+ CASE WHEN user_id = :author_id AND image_path IS NOT NULL THEN 1 ELSE 0 END '
                . 'FROM spots WHERE id = :spot_id'
            );
            $count->execute([
                'images_spot_id' => $spotId,
                'contributor_id' => $userId,
                'author_id' => $userId,
                'spot_id' => $spotId,
            ]);
            if ((int) $count->fetchColumn() + count($imagePaths) > 5) {
                throw new DomainException('Możesz mieć maksymalnie 5 własnych zdjęć przy jednym spocie. Usuń jedno ze swoich zdjęć, aby dodać kolejne.');
            }

            $insert = $this->database->prepare(
                'INSERT INTO spot_images (spot_id, image_path, contributor_user_id, status) '
                . 'VALUES (:spot_id, :image_path, :user_id, :status) RETURNING id'
            );
            $imageIds = [];
            foreach ($imagePaths as $imagePath) {
                $insert->execute(['spot_id' => $spotId, 'image_path' => $imagePath, 'user_id' => $userId, 'status' => $status]);
                $imageIds[] = (int) $insert->fetchColumn();
            }
            $this->database->commit();

            return $imageIds;
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function moderateImageContribution(int $imageId, bool $approve): ?array
    {
        $status = $approve ? 'approved' : 'rejected';
        $statement = $this->database->prepare(
            'UPDATE spot_images SET status = :status WHERE id = :id AND status = \'pending\' '
            . 'RETURNING contributor_user_id, spot_id'
        );
        $statement->execute(['id' => $imageId, 'status' => $status]);
        $row = $statement->fetch();

        return $row === false ? null : [
            'contributor_user_id' => $row['contributor_user_id'] === null ? null : (int) $row['contributor_user_id'],
            'spot_id' => (int) $row['spot_id'],
        ];
    }

    public function approve(int $spotId): bool
    {
        $statement = $this->database->prepare("UPDATE spots SET status = 'approved', rejection_reason = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND status = 'pending'");
        $statement->execute(['id' => $spotId]);

        return $statement->rowCount() === 1;
    }

    public function reject(int $spotId, string $reason): bool
    {
        $statement = $this->database->prepare("UPDATE spots SET status = 'rejected', rejection_reason = :reason, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND status = 'pending'");
        $statement->execute(['id' => $spotId, 'reason' => $reason]);

        return $statement->rowCount() === 1;
    }

    /** @param array<int, string> $imagePaths */
    public function saveAdditionalImages(int $spotId, array $imagePaths, int $authorUserId): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO spot_images (spot_id, image_path, contributor_user_id, status) '
            . 'VALUES (:spot_id, :image_path, :user_id, \'approved\')'
        );
        foreach ($imagePaths as $imagePath) {
            $statement->execute(['spot_id' => $spotId, 'image_path' => $imagePath, 'user_id' => $authorUserId]);
        }
    }

    /** @return array<int, array{path:string, author_name:?string, image_id:?int, contributor_user_id:?int, is_cover:bool}> */
    public function findGalleryImages(Spot $spot): array
    {
        $images = [];
        if ($spot->getImagePath() !== null) {
            $images[] = ['path' => $spot->getImagePath(), 'author_name' => $spot->getAuthorName(), 'image_id' => null, 'contributor_user_id' => $spot->getUserId(), 'is_cover' => true];
        }
        $statement = $this->database->prepare(
            'SELECT si.id, si.image_path, si.contributor_user_id, u.name AS author_name FROM spot_images si '
            . 'LEFT JOIN users u ON u.id = si.contributor_user_id '
            . 'WHERE si.spot_id = :spot_id AND si.status = \'approved\' ORDER BY si.created_at ASC, si.id ASC'
        );
        $statement->execute(['spot_id' => $spot->getId()]);
        foreach ($statement->fetchAll() as $row) {
            $images[] = [
                'path' => (string) $row['image_path'],
                'author_name' => $row['author_name'] === null ? null : (string) $row['author_name'],
                'image_id' => (int) $row['id'],
                'contributor_user_id' => $row['contributor_user_id'] === null ? null : (int) $row['contributor_user_id'],
                'is_cover' => false,
            ];
        }

        return $images;
    }

    /** @return array<int, array{id:int, path:string, status:string, author_name:?string}> */
    public function findOwnUnpublishedImages(int $spotId, int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT si.id, si.image_path, si.status, u.name AS author_name FROM spot_images si '
            . 'LEFT JOIN users u ON u.id = si.contributor_user_id '
            . 'WHERE si.spot_id = :spot_id AND si.contributor_user_id = :user_id AND si.status <> \'approved\' '
            . 'ORDER BY si.created_at ASC, si.id ASC'
        );
        $statement->execute(['spot_id' => $spotId, 'user_id' => $userId]);

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'path' => (string) $row['image_path'],
            'status' => (string) $row['status'],
            'author_name' => $row['author_name'] === null ? null : (string) $row['author_name'],
        ], $statement->fetchAll());
    }

    public function countOwnImages(int $spotId, int $userId): int
    {
        $statement = $this->database->prepare(
            'SELECT (SELECT COUNT(*) FROM spot_images WHERE spot_id = :images_spot_id AND contributor_user_id = :contributor_id) '
            . '+ CASE WHEN user_id = :author_id AND image_path IS NOT NULL THEN 1 ELSE 0 END '
            . 'FROM spots WHERE id = :spot_id'
        );
        $statement->execute([
            'images_spot_id' => $spotId,
            'contributor_id' => $userId,
            'author_id' => $userId,
            'spot_id' => $spotId,
        ]);

        return (int) $statement->fetchColumn();
    }

    /** @return array{spot_id:int, image_path:string}|null */
    public function deleteOwnImage(int $imageId, int $userId): ?array
    {
        $statement = $this->database->prepare(
            'DELETE FROM spot_images WHERE id = :image_id AND contributor_user_id = :user_id RETURNING spot_id, image_path'
        );
        $statement->execute(['image_id' => $imageId, 'user_id' => $userId]);
        $row = $statement->fetch();

        return $row === false ? null : ['spot_id' => (int) $row['spot_id'], 'image_path' => (string) $row['image_path']];
    }

    /** @return array{spot_id:int, image_path:string}|null */
    public function deleteOwnCoverImage(int $spotId, int $userId): ?array
    {
        $statement = $this->database->prepare(
            'UPDATE spots SET image_path = NULL WHERE id = :spot_id AND user_id = :user_id AND image_path IS NOT NULL RETURNING image_path'
        );
        $statement->execute(['spot_id' => $spotId, 'user_id' => $userId]);
        $imagePath = $statement->fetchColumn();

        return $imagePath === false ? null : ['spot_id' => $spotId, 'image_path' => (string) $imagePath];
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

    /** @param array<int, string> $categories */
    public function updateCategory(int $spotId, array $categories): bool
    {
        $statement = $this->database->prepare('UPDATE spots SET category = :category, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $statement->execute(['id' => $spotId, 'category' => json_encode($categories, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)]);
        return $statement->rowCount() === 1;
    }

    public function updateLocation(int $spotId, float $latitude, float $longitude): bool
    {
        $statement = $this->database->prepare('UPDATE spots SET latitude = :latitude, longitude = :longitude, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $statement->execute(['id' => $spotId, 'latitude' => $latitude, 'longitude' => $longitude]);
        return $statement->rowCount() === 1;
    }

    /** @param array<int, string> $categories */
    public function updateMetadata(int $spotId, string $title, array $categories, float $latitude, float $longitude): bool
    {
        $statement = $this->database->prepare(
            'UPDATE spots SET title = :title, category = :category, latitude = :latitude, longitude = :longitude, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute([
            'id' => $spotId,
            'title' => $title,
            'category' => json_encode($categories, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
        return $statement->rowCount() === 1;
    }

    /** @return array{spot_id:int, image_path:string, author_id:?int}|null */
    public function deletePhotoForModeration(int $spotId, string $photoKey): ?array
    {
        if ($photoKey === 'cover') {
            $statement = $this->database->prepare(
                'UPDATE spots SET image_path = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :spot_id AND image_path IS NOT NULL RETURNING id AS spot_id, image_path, user_id AS author_id'
            );
        } else {
            $imageId = filter_var($photoKey, FILTER_VALIDATE_INT);
            if ($imageId === false || $imageId < 1) {
                return null;
            }
            $statement = $this->database->prepare(
                'DELETE FROM spot_images WHERE id = :image_id AND spot_id = :spot_id AND status = \'approved\' RETURNING spot_id, image_path, contributor_user_id AS author_id'
            );
            $statement->execute(['image_id' => $imageId, 'spot_id' => $spotId]);
            $row = $statement->fetch();
            return $row === false ? null : [
                'spot_id' => (int) $row['spot_id'],
                'image_path' => (string) $row['image_path'],
                'author_id' => $row['author_id'] === null ? null : (int) $row['author_id'],
            ];
        }
        $statement->execute(['spot_id' => $spotId]);
        $row = $statement->fetch();
        return $row === false ? null : [
            'spot_id' => (int) $row['spot_id'],
            'image_path' => (string) $row['image_path'],
            'author_id' => $row['author_id'] === null ? null : (int) $row['author_id'],
        ];
    }

    /** @return array{spot_id:int, author_id:?int}|null */
    public function findPhotoOwnership(int $spotId, string $photoKey): ?array
    {
        if ($photoKey === 'cover') {
            $statement = $this->database->prepare('SELECT id AS spot_id, user_id AS author_id FROM spots WHERE id = :spot_id AND image_path IS NOT NULL');
            $statement->execute(['spot_id' => $spotId]);
        } else {
            $imageId = filter_var($photoKey, FILTER_VALIDATE_INT);
            if ($imageId === false || $imageId < 1) { return null; }
            $statement = $this->database->prepare('SELECT spot_id, contributor_user_id AS author_id FROM spot_images WHERE id = :image_id AND spot_id = :spot_id AND status = \'approved\'');
            $statement->execute(['image_id' => $imageId, 'spot_id' => $spotId]);
        }
        $row = $statement->fetch();
        return $row === false ? null : [
            'spot_id' => (int) $row['spot_id'],
            'author_id' => $row['author_id'] === null ? null : (int) $row['author_id'],
        ];
    }

    /** Delete an approved gallery photo or the cover photo; return its file path. */
    public function deleteReportedPhoto(int $spotId, string $photoKey): ?string
    {
        if ($photoKey === 'cover') {
            $statement = $this->database->prepare('UPDATE spots SET image_path = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :spot_id AND image_path IS NOT NULL RETURNING image_path');
            $statement->execute(['spot_id' => $spotId]);
            $path = $statement->fetchColumn();
            return $path === false ? null : (string) $path;
        }
        $imageId = filter_var($photoKey, FILTER_VALIDATE_INT);
        if ($imageId === false || $imageId < 1) { return null; }
        $statement = $this->database->prepare('DELETE FROM spot_images WHERE id = :id AND spot_id = :spot_id AND status = \'approved\' RETURNING image_path');
        $statement->execute(['id' => $imageId, 'spot_id' => $spotId]);
        $path = $statement->fetchColumn();
        return $path === false ? null : (string) $path;
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
        $spot->setRejectionReason(isset($row['rejection_reason']) && $row['rejection_reason'] !== null ? (string) $row['rejection_reason'] : null);
        $spot->setCreatedAt(new DateTimeImmutable((string) $row['created_at']));
        $spot->setUpdatedAt(new DateTimeImmutable((string) $row['updated_at']));
        $spot->setLatitude($row['latitude'] === null ? null : (float) $row['latitude']);
        $spot->setLongitude($row['longitude'] === null ? null : (float) $row['longitude']);
        $spot->setCategory($row['category'] === null ? null : (string) $row['category']);
        $spot->setImagePath($row['image_path'] === null ? null : (string) $row['image_path']);
        $spot->setPreviewImagePath(isset($row['preview_image_path']) && $row['preview_image_path'] !== null ? (string) $row['preview_image_path'] : null);
        $spot->setUserId($row['user_id'] === null ? null : (int) $row['user_id']);
        $spot->setAuthorName($row['author_name'] === null ? null : (string) $row['author_name']);
        $spot->setAuthorAvatarPath($row['author_avatar_path'] === null ? null : (string) $row['author_avatar_path']);

        return $spot;
    }
}
