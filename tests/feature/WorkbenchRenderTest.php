<?php

declare(strict_types=1);

use App\Core\Request;

test('工作台首页能渲染关键区块', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 1;

    ob_start();
    $app->handle(new Request('GET', '/'));
    $output = (string) ob_get_clean();

    assert_contains('待回访与提醒', $output);
    assert_contains('营销排期风险', $output);
    assert_contains('KPI 快照', $output);

    @unlink($dbPath);
});
