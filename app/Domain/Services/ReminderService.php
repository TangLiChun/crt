<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\ContactRepository;
use App\Domain\Repositories\ReminderRepository;

final class ReminderService
{
    public function __construct(
        private readonly ReminderRepository $reminders,
        private readonly ContactRepository $contacts,
        private readonly AccessService $access,
    ) {
    }

    public function list(array $user): array
    {
        $items = $this->reminders->listOpen($this->access->scope($user));
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Shanghai'));

        return array_map(static function (array $reminder) use ($now): array {
            $dueAt = new \DateTimeImmutable($reminder['due_at'], new \DateTimeZone('Asia/Shanghai'));
            $hours = (int) floor(($dueAt->getTimestamp() - $now->getTimestamp()) / 3600);
            $reminder['urgency'] = $hours < 0 ? 'overdue' : ($hours <= 24 ? 'today' : 'upcoming');
            $reminder['hours_left'] = $hours;

            return $reminder;
        }, $items);
    }

    public function stats(array $user): array
    {
        return $this->reminders->summary($this->access->scope($user));
    }

    public function complete(int $id, array $user): void
    {
        if ($this->reminders->find($id, $this->access->scope($user)) === null) {
            return;
        }

        $this->reminders->updateStatus($id, 'completed');
    }

    public function dismiss(int $id, array $user): void
    {
        if ($this->reminders->find($id, $this->access->scope($user)) === null) {
            return;
        }

        $this->reminders->updateStatus($id, 'dismissed');
    }
}
