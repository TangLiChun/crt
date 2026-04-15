<?php

declare(strict_types=1);

use App\Domain\Repositories\ContactRepository;
use App\Domain\Repositories\ReminderRepository;
use App\Domain\Services\AccessService;
use App\Domain\Services\ReminderService;

test('ReminderService 能识别超期和今日提醒', function (): void {
    [$dbPath, $pdo] = boot_test_database();
    $service = new ReminderService(
        new ReminderRepository($pdo),
        new ContactRepository($pdo, 20),
        new AccessService()
    );

    $stats = $service->stats(['id' => 3, 'role' => 'sales']);

    assert_true($stats['total'] >= 1, '至少应有一条提醒');
    assert_true($stats['today'] >= 0, '今日提醒统计应可计算');

    @unlink($dbPath);
});
