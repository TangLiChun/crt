<?php

declare(strict_types=1);

use App\Core\Request;

test('销售账号只能看到自己负责的客户', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 2;

    ob_start();
    $app->handle(new Request('GET', '/contacts'));
    $output = (string) ob_get_clean();

    assert_contains('张晨', $output);
    assert_true(!str_contains($output, '周浩'), '销售账号不应看到他人客户');

    @unlink($dbPath);
});

test('管理层可以看到跨成员营销排期', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 1;

    ob_start();
    $app->handle(new Request('GET', '/marketing-posts'));
    $output = (string) ob_get_clean();

    assert_contains('CRM 跟进模板分享', $output);
    assert_contains('销售工作台界面灵感', $output);

    @unlink($dbPath);
});
