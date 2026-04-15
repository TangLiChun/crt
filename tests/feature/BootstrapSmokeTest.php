<?php

declare(strict_types=1);

use App\Core\Request;

test('应用入口可以渲染登录页', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);

    ob_start();
    $app->handle(new Request('GET', '/login'));
    $output = (string) ob_get_clean();

    assert_contains('进入工作台', $output);
    assert_contains('演示账号', $output);

    @unlink($dbPath);
});
