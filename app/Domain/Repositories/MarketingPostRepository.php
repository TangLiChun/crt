<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use PDO;

final class MarketingPostRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(array $scope = []): array
    {
        $sql = 'SELECT marketing_posts.*, users.display_name AS creator_name
                FROM marketing_posts
                JOIN users ON users.id = marketing_posts.creator_user_id
                WHERE 1 = 1';
        $params = [];

        $sql = $this->applyScope($sql, $params, $scope);
        $sql .= ' ORDER BY planned_at ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id, array $scope = []): ?array
    {
        $sql = 'SELECT marketing_posts.*, users.display_name AS creator_name
                FROM marketing_posts
                JOIN users ON users.id = marketing_posts.creator_user_id
                WHERE marketing_posts.id = :id';
        $params = ['id' => $id];

        $sql = $this->applyScope($sql, $params, $scope);
        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO marketing_posts (
                creator_user_id, posting_rule_id, channel_name, title, content, planned_at,
                published_at, status, min_gap_hours, created_at, updated_at
             ) VALUES (
                :creator_user_id, :posting_rule_id, :channel_name, :title, :content, :planned_at,
                :published_at, :status, :min_gap_hours, :created_at, :updated_at
             )'
        );
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE marketing_posts
             SET posting_rule_id = :posting_rule_id,
                 channel_name = :channel_name,
                 title = :title,
                 content = :content,
                 planned_at = :planned_at,
                 published_at = :published_at,
                 status = :status,
                 min_gap_hours = :min_gap_hours,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function upcoming(int $limit = 5, array $scope = []): array
    {
        $sql = 'SELECT * FROM marketing_posts
                WHERE status IN ("planned", "scheduled")';
        $params = [];

        $sql = $this->applyScope($sql, $params, $scope, 'creator_user_id');
        $sql .= ' ORDER BY planned_at ASC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function publishStats(string $since, array $scope = []): array
    {
        $sql = 'SELECT
                    COUNT(*) AS planned_count,
                    SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) AS published_count,
                    SUM(CASE WHEN planned_at < CURRENT_TIMESTAMP AND status != "published" THEN 1 ELSE 0 END) AS delayed_count
                FROM marketing_posts
                WHERE planned_at >= :since';
        $params = ['since' => $since];

        $sql = $this->applyScope($sql, $params, $scope, 'creator_user_id');

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        return [
            'planned_count' => (int) ($row['planned_count'] ?? 0),
            'published_count' => (int) ($row['published_count'] ?? 0),
            'delayed_count' => (int) ($row['delayed_count'] ?? 0),
        ];
    }

    public function creatorBreakdownSince(string $since, array $scope = [], int $limit = 10): array
    {
        $sql = 'SELECT users.id,
                       users.display_name,
                       COUNT(marketing_posts.id) AS planned_count,
                       SUM(CASE WHEN marketing_posts.status = "published" THEN 1 ELSE 0 END) AS published_count,
                       MAX(marketing_posts.planned_at) AS latest_planned_at
                FROM users
                LEFT JOIN marketing_posts
                    ON marketing_posts.creator_user_id = users.id
                   AND marketing_posts.planned_at >= :since
                WHERE users.is_active = 1';
        $params = ['since' => $since];

        if (($scope['is_manager'] ?? false) !== true) {
            $sql .= ' AND users.id = :scope_user_id';
            $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);
        }

        $sql .= ' GROUP BY users.id
                  ORDER BY published_count DESC, planned_count DESC, users.display_name ASC
                  LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function applyScope(string $sql, array &$params, array $scope, string $ownerColumn = 'marketing_posts.creator_user_id'): string
    {
        if (($scope['is_manager'] ?? false) === true) {
            return $sql;
        }

        $sql .= sprintf(' AND %s = :scope_user_id', $ownerColumn);
        $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);

        return $sql;
    }
}
