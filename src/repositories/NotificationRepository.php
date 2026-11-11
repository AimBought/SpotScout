<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Notification;
use DateTimeImmutable;
use PDO;

final class NotificationRepository
{
    public function __construct(private PDO $database)
    {
    }

    public function create(int $userId, string $message): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO notifications (user_id, message) VALUES (:user_id, :message) RETURNING id'
        );
        $statement->execute(['user_id' => $userId, 'message' => $message]);

        return (int) $statement->fetchColumn();
    }

    public function notifyModeratorsAboutPendingSpot(int $spotId, string $title): void
    {
        $message = 'New spot "' . $title . '" is awaiting moderation. (#' . $spotId . ')';
        $statement = $this->database->prepare(
            'INSERT INTO notifications (user_id, message) '
            . 'SELECT u.id, :message_insert FROM users u '
            . 'WHERE u.role IN (\'moderator\', \'admin\') '
            . 'AND NOT EXISTS (SELECT 1 FROM notifications n WHERE n.user_id = u.id AND n.message = :message_check)'
        );
        $statement->execute(['message_insert' => $message, 'message_check' => $message]);
    }

    public function notifyModeratorsAboutPendingImage(int $imageId, string $spotTitle, int $contributorId): void
    {
        $message = 'New photo for "' . $spotTitle . '" is awaiting moderation. (#' . $imageId . ')';
        $statement = $this->database->prepare(
            'INSERT INTO notifications (user_id, message) '
            . 'SELECT u.id, :message_insert FROM users u '
            . 'WHERE u.role IN (\'moderator\', \'admin\') AND u.id <> :contributor_id '
            . 'AND NOT EXISTS (SELECT 1 FROM notifications n WHERE n.user_id = u.id AND n.message = :message_check)'
        );
        $statement->execute([
            'message_insert' => $message,
            'message_check' => $message,
            'contributor_id' => $contributorId,
        ]);
    }

    /** @return array<int, Notification> */
    public function findUnreadByUserId(int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT id, user_id, message, is_read, created_at FROM notifications WHERE user_id = :user_id AND is_read = FALSE ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(static fn (array $row): Notification => new Notification(
            (int) $row['id'],
            (int) $row['user_id'],
            (string) $row['message'],
            in_array($row['is_read'], [true, 1, '1', 't'], true),
            new DateTimeImmutable((string) $row['created_at']),
        ), $statement->fetchAll());
    }

    public function countUnreadByUserId(int $userId): int
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = FALSE');
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        $statement = $this->database->prepare('UPDATE notifications SET is_read = TRUE WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $notificationId, 'user_id' => $userId]);
    }
}
