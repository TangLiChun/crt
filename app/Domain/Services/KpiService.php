<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\ContactRepository;
use App\Domain\Repositories\FollowUpRepository;
use App\Domain\Repositories\MarketingPostRepository;
use App\Domain\Repositories\ReminderRepository;

final class KpiService
{
    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly FollowUpRepository $followUps,
        private readonly ReminderRepository $reminders,
        private readonly MarketingPostRepository $posts,
        private readonly AccessService $access,
    ) {
    }

    public function snapshot(array $user): array
    {
        $scope = $this->access->scope($user);
        $windowStart = $this->windowStart();
        $contactStats = $this->contacts->stats($scope);
        $reminderStats = $this->reminders->summary($scope);
        $followUpCount = $this->followUps->countSince($windowStart, $scope);
        $postStats = $this->posts->publishStats($windowStart, $scope);
        $conversionRate = percentage($contactStats['customers'], $contactStats['total']);
        $publishRate = percentage($postStats['published_count'], $postStats['planned_count']);

        return [
            'scopeLabel' => $this->access->isManager($user) ? '团队全量视角' : '仅查看我负责的数据',
            'periodLabel' => '近 30 天',
            'cards' => [
                [
                    'label' => $this->access->isManager($user) ? '团队转化率' : '我的转化率',
                    'value' => $conversionRate . '%',
                    'tone' => $conversionRate >= 45 ? 'success' : 'info',
                ],
                [
                    'label' => '近 30 天跟进',
                    'value' => $followUpCount,
                    'tone' => 'info',
                ],
                [
                    'label' => '逾期提醒',
                    'value' => $reminderStats['overdue'],
                    'tone' => $reminderStats['overdue'] > 0 ? 'warning' : 'success',
                ],
                [
                    'label' => '发帖完成率',
                    'value' => $publishRate . '%',
                    'tone' => $publishRate >= 70 ? 'success' : 'warning',
                ],
            ],
            'highlights' => [
                sprintf('当前客户池包含 %d 个潜客、%d 个现有客户。', $contactStats['leads'], $contactStats['customers']),
                sprintf('开放提醒 %d 条，其中 %d 条已经超期。', $reminderStats['total'], $reminderStats['overdue']),
                sprintf('近 30 天计划 %d 篇营销内容，已发布 %d 篇。', $postStats['planned_count'], $postStats['published_count']),
            ],
        ];
    }

    public function dashboard(array $user): array
    {
        $scope = $this->access->scope($user);
        $windowStart = $this->windowStart();
        $contactStats = $this->contacts->stats($scope);
        $reminderStats = $this->reminders->summary($scope);
        $postStats = $this->posts->publishStats($windowStart, $scope);
        $followUpCount = $this->followUps->countSince($windowStart, $scope);

        return array_merge($this->snapshot($user), [
            'contactStats' => $contactStats,
            'reminderStats' => $reminderStats,
            'followUpCount' => $followUpCount,
            'postStats' => $postStats,
            'pipeline' => $this->buildPipeline($this->contacts->stageBreakdown($scope), $contactStats['total']),
            'leaderboard' => $this->buildLeaderboard(
                $this->contacts->ownerBreakdown($scope),
                $this->followUps->leaderboardSince($windowStart, $scope),
                $this->posts->creatorBreakdownSince($windowStart, $scope)
            ),
            'executionRows' => [
                [
                    'label' => '待回访压力',
                    'value' => $contactStats['due_follow_ups'],
                    'note' => '当前需要推进的客户回访数量',
                    'tone' => $contactStats['due_follow_ups'] > 0 ? 'warning' : 'success',
                ],
                [
                    'label' => '今日提醒',
                    'value' => $reminderStats['today'],
                    'note' => '今天内需要处理的开放提醒',
                    'tone' => $reminderStats['today'] > 0 ? 'warning' : 'success',
                ],
                [
                    'label' => '已延迟排期',
                    'value' => $postStats['delayed_count'],
                    'note' => '计划时间已到但仍未发布的内容',
                    'tone' => $postStats['delayed_count'] > 0 ? 'danger' : 'success',
                ],
            ],
        ]);
    }

    private function buildPipeline(array $rows, int $total): array
    {
        $items = [];

        foreach ($rows as $row) {
            $count = (int) ($row['total'] ?? 0);
            $items[] = [
                'label' => sprintf(
                    '%s · %s',
                    (string) ($row['stage'] ?? '未命名阶段'),
                    ($row['contact_type'] ?? 'lead') === 'lead' ? '潜客' : '客户'
                ),
                'count' => $count,
                'width' => max(10, percentage($count, max($total, 1))),
            ];
        }

        return $items;
    }

    private function buildLeaderboard(array $contactRows, array $followUpRows, array $postRows): array
    {
        $entries = [];

        foreach ($contactRows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $entries[$id] = [
                'display_name' => (string) ($row['display_name'] ?? '未命名成员'),
                'role' => (string) ($row['role'] ?? 'sales'),
                'total_contacts' => (int) ($row['total_contacts'] ?? 0),
                'leads' => (int) ($row['leads'] ?? 0),
                'customers' => (int) ($row['customers'] ?? 0),
                'due_follow_ups' => (int) ($row['due_follow_ups'] ?? 0),
                'follow_up_count' => 0,
                'planned_count' => 0,
                'published_count' => 0,
            ];
        }

        foreach ($followUpRows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if (!isset($entries[$id])) {
                $entries[$id] = [
                    'display_name' => (string) ($row['display_name'] ?? '未命名成员'),
                    'role' => 'sales',
                    'total_contacts' => 0,
                    'leads' => 0,
                    'customers' => 0,
                    'due_follow_ups' => 0,
                    'follow_up_count' => 0,
                    'planned_count' => 0,
                    'published_count' => 0,
                ];
            }

            $entries[$id]['follow_up_count'] = (int) ($row['follow_up_count'] ?? 0);
        }

        foreach ($postRows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if (!isset($entries[$id])) {
                $entries[$id] = [
                    'display_name' => (string) ($row['display_name'] ?? '未命名成员'),
                    'role' => 'sales',
                    'total_contacts' => 0,
                    'leads' => 0,
                    'customers' => 0,
                    'due_follow_ups' => 0,
                    'follow_up_count' => 0,
                    'planned_count' => 0,
                    'published_count' => 0,
                ];
            }

            $entries[$id]['planned_count'] = (int) ($row['planned_count'] ?? 0);
            $entries[$id]['published_count'] = (int) ($row['published_count'] ?? 0);
        }

        foreach ($entries as &$entry) {
            $entry['conversion_rate'] = percentage($entry['customers'], max($entry['total_contacts'], 1));
            $entry['score'] = ($entry['customers'] * 4) + ($entry['follow_up_count'] * 2) + ($entry['published_count'] * 3) + $entry['leads'];
        }
        unset($entry);

        usort($entries, static function (array $left, array $right): int {
            return [$right['score'], $right['conversion_rate'], $right['follow_up_count'], $left['display_name']]
                <=> [$left['score'], $left['conversion_rate'], $left['follow_up_count'], $right['display_name']];
        });

        return array_slice($entries, 0, 10);
    }

    private function windowStart(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Shanghai')))
            ->modify('-30 days')
            ->format('Y-m-d H:i:s');
    }
}
