<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\ContactRepository;
use App\Domain\Repositories\FollowUpRepository;
use App\Domain\Repositories\ReminderRepository;
use InvalidArgumentException;

final class FollowUpService
{
    public function __construct(
        private readonly FollowUpRepository $followUps,
        private readonly ContactRepository $contacts,
        private readonly ReminderRepository $reminders,
        private readonly AccessService $access,
    ) {
    }

    public function listForContact(int $contactId): array
    {
        return $this->followUps->forContact($contactId);
    }

    public function create(int $contactId, array $user, array $payload): int
    {
        $content = trim((string) ($payload['content'] ?? ''));
        if ($content === '') {
            throw new InvalidArgumentException('跟进内容不能为空');
        }

        $contact = $this->contacts->find($contactId, $this->access->scope($user));
        if ($contact === null) {
            throw new InvalidArgumentException('客户不存在，或你没有权限补充该客户的跟进。');
        }

        $nextFollowUpAt = $payload['next_follow_up_at'] ?: null;
        $id = $this->followUps->create([
            'contact_id' => $contactId,
            'user_id' => (int) ($user['id'] ?? 0),
            'content' => $content,
            'outcome' => trim((string) ($payload['outcome'] ?? '')),
            'next_follow_up_at' => $nextFollowUpAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->contacts->touchFollowUp($contactId, $nextFollowUpAt);

        if ($contact !== null && $nextFollowUpAt !== null) {
            $this->reminders->clearOpenForSubject('contact', $contactId, 'follow_up');
            $this->reminders->create([
                'subject_type' => 'contact',
                'subject_id' => $contactId,
                'reminder_type' => 'follow_up',
                'title' => '跟进 ' . $contact['name'],
                'detail' => $content,
                'due_at' => $nextFollowUpAt,
                'completed_at' => null,
                'status' => 'open',
                'assigned_user_id' => (int) $contact['owner_user_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $id;
    }
}
