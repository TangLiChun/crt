<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use PDO;

final class ContactRepository
{
    public function __construct(private readonly PDO $pdo, private readonly int $pageSize = 20)
    {
    }

    public function search(array $filters = [], array $scope = []): array
    {
        $sql = 'SELECT contacts.*, users.display_name AS owner_name
                FROM contacts
                JOIN users ON users.id = contacts.owner_user_id
                WHERE 1 = 1';
        $params = [];

        $sql = $this->applyScope($sql, $params, $scope);

        if (($filters['q'] ?? '') !== '') {
            $sql .= ' AND (contacts.name LIKE :q OR contacts.company_name LIKE :q OR contacts.phone LIKE :q)';
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (($filters['contact_type'] ?? '') !== '') {
            $sql .= ' AND contacts.contact_type = :contact_type';
            $params['contact_type'] = $filters['contact_type'];
        }

        if (($filters['stage'] ?? '') !== '') {
            $sql .= ' AND contacts.stage = :stage';
            $params['stage'] = $filters['stage'];
        }

        if (($filters['follow_up'] ?? '') === 'due') {
            $sql .= ' AND contacts.next_follow_up_at IS NOT NULL AND contacts.next_follow_up_at <= :due_cutoff';
            $params['due_cutoff'] = date('Y-m-d H:i:s');
        }

        if (($scope['is_manager'] ?? false) === true && ($filters['owner_user_id'] ?? '') !== '') {
            $sql .= ' AND contacts.owner_user_id = :owner_user_id';
            $params['owner_user_id'] = (int) $filters['owner_user_id'];
        }

        $sql .= ' ORDER BY COALESCE(contacts.next_follow_up_at, contacts.updated_at) ASC, contacts.updated_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id, array $scope = []): ?array
    {
        $sql = 'SELECT contacts.*, users.display_name AS owner_name
                FROM contacts
                JOIN users ON users.id = contacts.owner_user_id
                WHERE contacts.id = :id';
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
            'INSERT INTO contacts (
                owner_user_id, contact_type, name, company_name, phone, email, source, stage, status,
                notes, last_contacted_at, next_follow_up_at, created_at, updated_at
            ) VALUES (
                :owner_user_id, :contact_type, :name, :company_name, :phone, :email, :source, :stage, :status,
                :notes, :last_contacted_at, :next_follow_up_at, :created_at, :updated_at
            )'
        );

        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE contacts SET
                contact_type = :contact_type,
                name = :name,
                company_name = :company_name,
                phone = :phone,
                email = :email,
                source = :source,
                stage = :stage,
                status = :status,
                notes = :notes,
                next_follow_up_at = :next_follow_up_at,
                updated_at = :updated_at
             WHERE id = :id'
        );

        $stmt->execute($data);
    }

    public function touchFollowUp(int $id, ?string $nextFollowUpAt): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE contacts
             SET last_contacted_at = :last_contacted_at,
                 next_follow_up_at = :next_follow_up_at,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'last_contacted_at' => date('Y-m-d H:i:s'),
            'next_follow_up_at' => $nextFollowUpAt,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function stats(array $scope = []): array
    {
        $sql = 'SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN contact_type = "lead" THEN 1 ELSE 0 END) AS leads,
                    SUM(CASE WHEN contact_type = "customer" THEN 1 ELSE 0 END) AS customers,
                    SUM(CASE WHEN next_follow_up_at IS NOT NULL AND next_follow_up_at <= CURRENT_TIMESTAMP THEN 1 ELSE 0 END) AS due_follow_ups
                FROM contacts
                WHERE 1 = 1';
        $params = [];

        $sql = $this->applyScope($sql, $params, $scope, 'owner_user_id');

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'leads' => (int) ($row['leads'] ?? 0),
            'customers' => (int) ($row['customers'] ?? 0),
            'due_follow_ups' => (int) ($row['due_follow_ups'] ?? 0),
        ];
    }

    public function recent(int $limit = 5, array $scope = []): array
    {
        $sql = 'SELECT contacts.*, users.display_name AS owner_name
                FROM contacts
                JOIN users ON users.id = contacts.owner_user_id
                WHERE 1 = 1';
        $params = [];

        $sql = $this->applyScope($sql, $params, $scope);
        $sql .= ' ORDER BY updated_at DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function stageBreakdown(array $scope = []): array
    {
        $sql = 'SELECT stage, contact_type, COUNT(*) AS total
                FROM contacts
                WHERE 1 = 1';
        $params = [];

        $sql = $this->applyScope($sql, $params, $scope, 'owner_user_id');
        $sql .= ' GROUP BY stage, contact_type ORDER BY total DESC, stage ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function ownerBreakdown(array $scope = [], int $limit = 10): array
    {
        $sql = 'SELECT users.id,
                       users.display_name,
                       users.role,
                       COUNT(contacts.id) AS total_contacts,
                       SUM(CASE WHEN contacts.contact_type = "lead" THEN 1 ELSE 0 END) AS leads,
                       SUM(CASE WHEN contacts.contact_type = "customer" THEN 1 ELSE 0 END) AS customers,
                       SUM(CASE WHEN contacts.next_follow_up_at IS NOT NULL AND contacts.next_follow_up_at <= CURRENT_TIMESTAMP THEN 1 ELSE 0 END) AS due_follow_ups
                FROM users
                LEFT JOIN contacts ON contacts.owner_user_id = users.id
                WHERE users.is_active = 1';
        $params = [];

        if (($scope['is_manager'] ?? false) !== true) {
            $sql .= ' AND users.id = :scope_user_id';
            $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);
        }

        $sql .= ' GROUP BY users.id
                  ORDER BY total_contacts DESC, customers DESC, users.display_name ASC
                  LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function applyScope(string $sql, array &$params, array $scope, string $ownerColumn = 'contacts.owner_user_id'): string
    {
        if (($scope['is_manager'] ?? false) === true) {
            return $sql;
        }

        $sql .= sprintf(' AND %s = :scope_user_id', $ownerColumn);
        $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);

        return $sql;
    }
}
