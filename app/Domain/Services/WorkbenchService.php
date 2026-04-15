<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\ContactRepository;

final class WorkbenchService
{
    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly ReminderService $reminders,
        private readonly MarketingPostService $marketingPosts,
        private readonly KpiService $kpis,
        private readonly AccessService $access,
    ) {
    }

    public function snapshot(array $user): array
    {
        $scope = $this->access->scope($user);
        $stats = $this->contacts->stats($scope);
        $reminderStats = $this->reminders->stats($user);
        $marketing = $this->marketingPosts->list($user);

        return [
            'stats' => [
                ['label' => '客户总数', 'value' => $stats['total'], 'tone' => 'neutral'],
                ['label' => '潜在客户', 'value' => $stats['leads'], 'tone' => 'info'],
                ['label' => '现有客户', 'value' => $stats['customers'], 'tone' => 'success'],
                ['label' => '待回访', 'value' => $stats['due_follow_ups'], 'tone' => 'warning'],
                ['label' => '提醒事项', 'value' => $reminderStats['total'], 'tone' => 'warning'],
            ],
            'dueReminders' => array_slice($this->reminders->list($user), 0, 6),
            'postAlerts' => $marketing['alerts'],
            'recentContacts' => $this->contacts->recent(5, $scope),
            'upcomingPosts' => $this->marketingPosts->upcoming(5, $user),
            'kpiSnapshot' => $this->kpis->snapshot($user),
        ];
    }
}
