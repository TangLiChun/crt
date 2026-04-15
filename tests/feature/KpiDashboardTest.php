<?php

declare(strict_types=1);

use App\Core\Request;

test('管理层可以看到团队 KPI 看板', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 1;

    ob_start();
    $app->handle(new Request('GET', '/kpi'));
    $output = (string) ob_get_clean();

    assert_contains('团队全量视角', $output);
    assert_contains('团队成员排行', $output);
    assert_contains('销售管理员', $output);

    @unlink($dbPath);
});

test('销售账号看到的是个人 KPI 视角', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 2;

    ob_start();
    $app->handle(new Request('GET', '/kpi'));
    $output = (string) ob_get_clean();

    assert_contains('仅查看我负责的数据', $output);
    assert_contains('我的 KPI 结构', $output);
    assert_contains('陈露', $output);

    @unlink($dbPath);
});
