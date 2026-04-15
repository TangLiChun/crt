<?php

declare(strict_types=1);

use App\Core\Request;

test('登录后的布局包含侧边导航', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 1;

    ob_start();
    $app->handle(new Request('GET', '/contacts'));
    $output = (string) ob_get_clean();

    assert_contains('工作台', $output);
    assert_contains('KPI 看板', $output);
    assert_contains('客户与潜客', $output);
    assert_contains('营销排期', $output);

    @unlink($dbPath);
});
