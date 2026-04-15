<?php

declare(strict_types=1);

test('种子数据包含默认管理员和客户样例', function (): void {
    [$dbPath, $pdo] = boot_test_database();

    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $contactCount = (int) $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();

    assert_true($userCount >= 3, '默认账号样例不足');
    assert_true($contactCount >= 3, '样例客户数据不足');

    @unlink($dbPath);
});
