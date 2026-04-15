<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use PDO;

final class FollowUpRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function forContact(int $contactId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT follow_up_records.*, users.display_name AS user_name
             FROM follow_up_records
             JOIN users ON users.id = follow_up_records.user_id
             WHERE contact_id = :contact_id
             ORDER BY created_at DESC'
        );
        $stmt->execute(['contact_id' => $contactId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO follow_up_records (contact_id, user_id, content, outcome, next_follow_up_at, created_at)
             VALUES (:contact_id, :user_id, :content, :outcome, :next_follow_up_at, :created_at)'
        );
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function countSince(string $since, array $scope = []): int
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM follow_up_records
                WHERE created_at >= :since';
        $params = ['since' => $since];

        if (($scope['is_manager'] ?? false) !== true) {
            $sql .= ' AND user_id = :scope_user_id';
            $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function leaderboardSince(string $since, array $scope = [], int $limit = 10): array
    {
        $sql = 'SELECT users.id,
                       users.display_name,
                       COUNT(follow_up_records.id) AS follow_up_count,
                       MAX(follow_up_records.created_at) AS latest_follow_up_at
                FROM users
                LEFT JOIN follow_up_records
                    ON follow_up_records.user_id = users.id
                   AND follow_up_records.created_at >= :since
                WHERE users.is_active = 1';
        $params = ['since' => $since];

        if (($scope['is_manager'] ?? false) !== true) {
            $sql .= ' AND users.id = :scope_user_id';
            $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);
        }

        $sql .= ' GROUP BY users.id
                  ORDER BY follow_up_count DESC, latest_follow_up_at DESC, users.display_name ASC
                  LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
