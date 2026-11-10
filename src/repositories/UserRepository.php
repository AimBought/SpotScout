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

    /** @return array{id: int|string, name: string, email: string, password_hash: string, avatar_path: ?string, role: string}|null */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, name, email, password_hash, avatar_path, role FROM users WHERE LOWER(email) = LOWER(:email)'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    /** @return array{id: int|string, name: string, email: string, password_hash: string, avatar_path: ?string, role: string}|null */
    public function findByEmailOrName(string $identifier): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, name, email, password_hash, avatar_path, role FROM users '
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
        $statement = $this->database->prepare('SELECT id, name, email, avatar_path, role FROM users WHERE id = :id');
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch();

        return $row === false
            ? null
            : new User((int) $row['id'], (string) $row['name'], (string) $row['email'], $row['avatar_path'] === null ? null : (string) $row['avatar_path'], (string) $row['role']);
    }

    public function updateAvatar(int $userId, string $path): void
    {
        $statement = $this->database->prepare('UPDATE users SET avatar_path = :avatar_path WHERE id = :id');
        $statement->execute(['id' => $userId, 'avatar_path' => $path]);
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (name, email, password_hash, role) '
            . 'VALUES (:name, :email, :password_hash, :role) RETURNING id'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => 'user',
        ]);

        return (int) $statement->fetchColumn();
    }

    /** @return array<int, User> */
    public function findAll(): array
    {
        $statement = $this->database->query('SELECT id, name, email, avatar_path, role FROM users ORDER BY id');
        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): User => new User(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            $row['avatar_path'] === null ? null : (string) $row['avatar_path'],
            (string) $row['role'],
        ), $rows);
    }

    public function updateRole(int $userId, string $role): void
    {
        if (!in_array($role, ['user', 'moderator', 'admin'], true)) {
            throw new \InvalidArgumentException('Invalid user role.');
        }

        $statement = $this->database->prepare('UPDATE users SET role = :role WHERE id = :id');
        $statement->execute(['id' => $userId, 'role' => $role]);
    }
}
