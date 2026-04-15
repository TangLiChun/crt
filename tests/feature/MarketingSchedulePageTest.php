<?php

declare(strict_types=1);

use App\Core\Request;

test('营销排期页包含视图切换和列表', function (): void {
    [$dbPath] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 1;

    ob_start();
    $app->handle(new Request('GET', '/marketing-posts'));
    $output = (string) ob_get_clean();

    assert_contains('月视图', $output);
    assert_contains('帖子排期与间隔提醒', $output);

    @unlink($dbPath);
});
