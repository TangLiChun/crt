<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\ContactRepository;
use InvalidArgumentException;

final class ContactService
{
    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly AccessService $access,
    ) {
    }

    public function list(array $filters, array $user): array
    {
        $scope = $this->access->scope($user);

        return [
            'contacts' => $this->contacts->search($filters, $scope),
            'stats' => $this->contacts->stats($scope),
        ];
    }

    public function find(int $id, array $user): ?array
    {
        return $this->contacts->find($id, $this->access->scope($user));
    }

    public function save(array $payload, array $user, ?int $contactId = null): int
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $contactType = (string) ($payload['contact_type'] ?? 'lead');
        $stage = trim((string) ($payload['stage'] ?? '新线索'));
        $actorId = (int) ($user['id'] ?? 0);

        if ($name === '') {
            throw new InvalidArgumentException('联系人姓名不能为空');
        }

        if (!in_array($contactType, ['lead', 'customer'], true)) {
            throw new InvalidArgumentException('客户类型不合法');
        }

        $data = [
            'owner_user_id' => $actorId,
            'contact_type' => $contactType,
            'name' => $name,
            'company_name' => trim((string) ($payload['company_name'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'source' => trim((string) ($payload['source'] ?? '')),
            'stage' => $stage,
            'status' => trim((string) ($payload['status'] ?? 'active')) ?: 'active',
            'notes' => trim((string) ($payload['notes'] ?? '')),
            'last_contacted_at' => ($payload['last_contacted_at'] ?? '') !== '' ? $payload['last_contacted_at'] : null,
            'next_follow_up_at' => ($payload['next_follow_up_at'] ?? '') !== '' ? $payload['next_follow_up_at'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($contactId !== null) {
            if ($this->find($contactId, $user) === null) {
                throw new InvalidArgumentException('客户不存在，或你没有权限修改该客户。');
            }

            unset($data['owner_user_id'], $data['created_at'], $data['last_contacted_at']);
            $this->contacts->update($contactId, $data);

            return $contactId;
        }

        return $this->contacts->create($data);
    }

    public function recent(int $limit, array $user): array
    {
        return $this->contacts->recent($limit, $this->access->scope($user));
    }
}
