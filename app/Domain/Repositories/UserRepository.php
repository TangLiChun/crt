<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);

        return $stmt->fetch() ?: null;
    }

    public function allActive(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, username, display_name, role
             FROM users
             WHERE is_active = 1
             ORDER BY CASE WHEN role = "manager" THEN 0 ELSE 1 END, display_name ASC'
        );

        return $stmt->fetchAll();
    }

    public function ensure(array $user): void
    {
        if ($this->findByUsername((string) $user['username']) !== null) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash, display_name, role, is_active, created_at, updated_at)
             VALUES (:username, :password_hash, :display_name, :role, 1, :created_at, :updated_at)'
        );

        $stmt->execute([
            'username' => $user['username'],
            'password_hash' => password_hash((string) $user['password'], PASSWORD_DEFAULT),
            'display_name' => $user['display_name'],
            'role' => $user['role'] ?? 'manager',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
