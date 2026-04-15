<?php

declare(strict_types=1);

use App\Core\Request;

test('登录后可以通过路由创建客户', function (): void {
    [$dbPath, $pdo] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 1;
    $token = $app->csrf()->token();

    ob_start();
    $app->handle(new Request('POST', '/contacts', [], [
        '_token' => $token,
        'name' => '王拓',
        'contact_type' => 'lead',
        'stage' => '新线索',
        'company_name' => '拓新咨询',
        'phone' => '13800000999',
    ]));
    ob_end_clean();

    $count = (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE name = '王拓'")->fetchColumn();
    assert_same(1, $count);

    @unlink($dbPath);
});
