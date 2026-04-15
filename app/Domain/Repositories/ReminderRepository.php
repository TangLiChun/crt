<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use PDO;

final class ReminderRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listOpen(array $scope = []): array
    {
        $sql = 'SELECT reminders.*, users.display_name AS assigned_user_name
                FROM reminders
                JOIN users ON users.id = reminders.assigned_user_id
                WHERE reminders.status = "open"';
        $params = [];

        if (($scope['is_manager'] ?? false) !== true) {
            $sql .= ' AND reminders.assigned_user_id = :scope_user_id';
            $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);
        }

        $sql .= ' ORDER BY reminders.due_at ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id, array $scope = []): ?array
    {
        $sql = 'SELECT reminders.*, users.display_name AS assigned_user_name
                FROM reminders
                JOIN users ON users.id = reminders.assigned_user_id
                WHERE reminders.id = :id';
        $params = ['id' => $id];

        if (($scope['is_manager'] ?? false) !== true) {
            $sql .= ' AND reminders.assigned_user_id = :scope_user_id';
            $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() ?: null;
    }

    public function summary(array $scope = []): array
    {
        $now = date('Y-m-d H:i:s');
        $endOfToday = date('Y-m-d 23:59:59');

        $sql = 'SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN due_at < :now THEN 1 ELSE 0 END) AS overdue,
                    SUM(CASE WHEN due_at >= :now AND due_at <= :end_of_today THEN 1 ELSE 0 END) AS today,
                    SUM(CASE WHEN due_at > :end_of_today THEN 1 ELSE 0 END) AS upcoming
                FROM reminders
                WHERE status = "open"';
        $params = [
            'now' => $now,
            'end_of_today' => $endOfToday,
        ];

        if (($scope['is_manager'] ?? false) !== true) {
            $sql .= ' AND assigned_user_id = :scope_user_id';
            $params['scope_user_id'] = (int) ($scope['user_id'] ?? 0);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'overdue' => (int) ($row['overdue'] ?? 0),
            'today' => (int) ($row['today'] ?? 0),
            'upcoming' => (int) ($row['upcoming'] ?? 0),
        ];
    }

    public function clearOpenForSubject(string $subjectType, int $subjectId, string $reminderType): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM reminders
             WHERE subject_type = :subject_type
               AND subject_id = :subject_id
               AND reminder_type = :reminder_type
               AND status = "open"'
        );
        $stmt->execute([
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'reminder_type' => $reminderType,
        ]);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reminders (
                subject_type, subject_id, reminder_type, title, detail, due_at, completed_at, status,
                assigned_user_id, created_at, updated_at
             ) VALUES (
                :subject_type, :subject_id, :reminder_type, :title, :detail, :due_at, :completed_at, :status,
                :assigned_user_id, :created_at, :updated_at
             )'
        );
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reminders
             SET status = :status,
                 completed_at = :completed_at,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'completed_at' => in_array($status, ['completed', 'dismissed'], true) ? date('Y-m-d H:i:s') : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
