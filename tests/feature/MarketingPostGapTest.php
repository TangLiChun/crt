<?php

declare(strict_types=1);

use App\Domain\Repositories\MarketingPostRepository;
use App\Domain\Repositories\PostingRuleRepository;
use App\Domain\Repositories\ReminderRepository;
use App\Domain\Services\AccessService;
use App\Domain\Services\MarketingPostService;

test('发帖过密时会产生预警', function (): void {
    [$dbPath, $pdo] = boot_test_database();
    $service = new MarketingPostService(
        new MarketingPostRepository($pdo),
        new PostingRuleRepository($pdo, ['default_min_gap_hours' => 72, 'default_stale_gap_hours' => 168]),
        new ReminderRepository($pdo),
        new AccessService()
    );

    $service->save([
        'channel_name' => '微信公众号',
        'title' => '过密测试',
        'content' => '用于测试过密预警',
        'planned_at' => '2026-04-15 11:00:00',
        'status' => 'planned',
    ], ['id' => 2, 'role' => 'sales']);

    $alerts = $service->list(['id' => 2, 'role' => 'sales'])['alerts'];
    assert_true(count($alerts) >= 1, '没有检测到过密提醒');

    @unlink($dbPath);
});
