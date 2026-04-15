<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\MarketingPostRepository;
use App\Domain\Repositories\PostingRuleRepository;
use App\Domain\Repositories\ReminderRepository;
use InvalidArgumentException;

final class MarketingPostService
{
    public function __construct(
        private readonly MarketingPostRepository $posts,
        private readonly PostingRuleRepository $rules,
        private readonly ReminderRepository $reminders,
        private readonly AccessService $access,
    ) {
    }

    public function list(array $user): array
    {
        $posts = $this->posts->all($this->access->scope($user));

        return [
            'posts' => $posts,
            'alerts' => $this->buildAlerts($posts),
            'calendar' => $this->buildCalendar($posts),
            'rules' => $this->rules->allActive(),
        ];
    }

    public function find(int $id, array $user): ?array
    {
        return $this->posts->find($id, $this->access->scope($user));
    }

    public function save(array $payload, array $user, ?int $postId = null): int
    {
        $channel = trim((string) ($payload['channel_name'] ?? ''));
        $title = trim((string) ($payload['title'] ?? ''));
        $plannedAt = trim((string) ($payload['planned_at'] ?? ''));

        if ($channel === '' || $title === '' || $plannedAt === '') {
            throw new InvalidArgumentException('渠道、标题和计划时间不能为空');
        }

        $rule = $this->rules->findByChannel($channel);
        $data = [
            'creator_user_id' => (int) ($user['id'] ?? 0),
            'posting_rule_id' => $rule['id'] ?? null,
            'channel_name' => $channel,
            'title' => $title,
            'content' => trim((string) ($payload['content'] ?? '')),
            'planned_at' => $plannedAt,
            'published_at' => ($payload['published_at'] ?? '') !== '' ? $payload['published_at'] : null,
            'status' => trim((string) ($payload['status'] ?? 'planned')) ?: 'planned',
            'min_gap_hours' => (int) ($rule['min_gap_hours'] ?? 72),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($postId !== null) {
            if ($this->find($postId, $user) === null) {
                throw new InvalidArgumentException('排期不存在，或你没有权限修改该排期。');
            }

            unset($data['creator_user_id'], $data['created_at']);
            $this->posts->update($postId, $data);

            return $postId;
        }

        return $this->posts->create($data);
    }

    public function upcoming(int $limit, array $user): array
    {
        return $this->posts->upcoming($limit, $this->access->scope($user));
    }

    public function buildAlerts(array $posts): array
    {
        $alerts = [];
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Shanghai'));
        $grouped = [];

        foreach ($posts as $post) {
            $grouped[$post['channel_name']][] = $post;
        }

        foreach ($grouped as $channel => $items) {
            usort($items, static fn(array $left, array $right): int => strcmp($left['planned_at'], $right['planned_at']));
            $rule = $this->rules->findByChannel($channel);
            $minGap = (int) ($rule['min_gap_hours'] ?? 72);
            $maxGap = (int) ($rule['max_gap_hours'] ?? 168);

            for ($index = 1, $count = count($items); $index < $count; $index++) {
                $previous = new \DateTimeImmutable($items[$index - 1]['planned_at']);
                $current = new \DateTimeImmutable($items[$index]['planned_at']);
                $gapHours = (int) floor(($current->getTimestamp() - $previous->getTimestamp()) / 3600);

                if ($gapHours < $minGap) {
                    $alerts[] = [
                        'tone' => 'warning',
                        'channel_name' => $channel,
                        'title' => '发帖间隔过密',
                        'message' => sprintf('%s 两篇内容间隔仅 %d 小时，低于规则 %d 小时。', $channel, $gapHours, $minGap),
                        'related_post_id' => (int) $items[$index]['id'],
                    ];
                }
            }

            $latest = end($items);
            if ($latest !== false) {
                $latestTime = new \DateTimeImmutable($latest['planned_at']);
                $hours = (int) floor(($now->getTimestamp() - $latestTime->getTimestamp()) / 3600);
                if ($hours > $maxGap) {
                    $alerts[] = [
                        'tone' => 'danger',
                        'channel_name' => $channel,
                        'title' => '出现断更风险',
                        'message' => sprintf('%s 已经 %d 小时没有新内容，超过规则 %d 小时。', $channel, $hours, $maxGap),
                        'related_post_id' => (int) $latest['id'],
                    ];
                }
            }
        }

        return $alerts;
    }

    public function buildCalendar(array $posts): array
    {
        $calendar = [];

        foreach ($posts as $post) {
            $day = substr((string) $post['planned_at'], 0, 10);
            $calendar[$day][] = $post;
        }

        ksort($calendar);

        return $calendar;
    }
}
