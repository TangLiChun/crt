<?php

declare(strict_types=1);

namespace App\Domain\Services;

final class AccessService
{
    public function isManager(?array $user): bool
    {
        return ($user['role'] ?? null) === 'manager';
    }

    public function scope(?array $user): array
    {
        return [
            'user_id' => (int) ($user['id'] ?? 0),
            'role' => (string) ($user['role'] ?? 'sales'),
            'is_manager' => $this->isManager($user),
        ];
    }

    public function canAccessUserOwnedRecord(?array $user, ?int $ownerUserId): bool
    {
        if ($ownerUserId === null) {
            return false;
        }

        return $this->isManager($user) || (int) ($user['id'] ?? 0) === $ownerUserId;
    }
}
