<?php

declare(strict_types=1);

test('数据库初始化后包含核心表', function (): void {
    [$dbPath, $pdo] = boot_test_database();
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);

    foreach (['users', 'contacts', 'follow_up_records', 'marketing_posts', 'posting_rules', 'reminders'] as $table) {
        assert_true(in_array($table, $tables, true), '缺少表 ' . $table);
    }

    @unlink($dbPath);
});
