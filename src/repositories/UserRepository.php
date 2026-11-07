<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;
use PDO;

final class UserRepository
{
    public function __construct(private PDO $database)
    {
    }

    /** @return array{id: int|string, name: string, email: string, password_hash: string, avatar_path: ?string}|null */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, name, email, password_hash, avatar_path FROM users WHERE LOWER(email) = LOWER(:email)'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    /** @return array{id: int|string, name: string, email: string, password_hash: string, avatar_path: ?string}|null */
    public function findByEmailOrName(string $identifier): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, name, email, password_hash, avatar_path FROM users '
            . 'WHERE LOWER(email) = LOWER(:email) OR LOWER(name) = LOWER(:name) '
            . 'ORDER BY CASE WHEN LOWER(email) = LOWER(:email_priority) THEN 0 ELSE 1 END '
            . 'LIMIT 1'
        );
        $statement->execute([
            'email' => $identifier,
            'name' => $identifier,
            'email_priority' => $identifier,
        ]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findById(int $userId): ?User
    {
        $statement = $this->database->prepare('SELECT id, name, email, avatar_path FROM users WHERE id = :id');
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch();

        return $row === false
            ? null
            : new User((int) $row['id'], (string) $row['name'], (string) $row['email'], $row['avatar_path'] === null ? null : (string) $row['avatar_path']);
    }

    public function updateAvatar(int $userId, string $path): void
    {
        $statement = $this->database->prepare('UPDATE users SET avatar_path = :avatar_path WHERE id = :id');
        $statement->execute(['id' => $userId, 'avatar_path' => $path]);
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (name, email, password_hash) '
            . 'VALUES (:name, :email, :password_hash) RETURNING id'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return (int) $statement->fetchColumn();
    }
}
