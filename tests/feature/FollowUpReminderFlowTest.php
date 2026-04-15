<?php

declare(strict_types=1);

use App\Core\Request;

test('新增跟进后会写入时间轴和提醒', function (): void {
    [$dbPath, $pdo] = boot_test_database();
    $app = make_app($dbPath);
    $_SESSION['user_id'] = 1;
    $token = $app->csrf()->token();

    ob_start();
    $app->handle(new Request('POST', '/contacts/1/follow-ups', [], [
        '_token' => $token,
        'content' => '再次沟通并约定演示时间。',
        'outcome' => '已约演示',
        'next_follow_up_at' => '2026-04-20 10:00:00',
    ]));
    ob_end_clean();

    $followUpCount = (int) $pdo->query('SELECT COUNT(*) FROM follow_up_records WHERE contact_id = 1')->fetchColumn();
    $reminderCount = (int) $pdo->query("SELECT COUNT(*) FROM reminders WHERE subject_type = 'contact' AND subject_id = 1 AND status = 'open'")->fetchColumn();

    assert_true($followUpCount >= 2, '跟进记录未新增');
    assert_true($reminderCount >= 1, '提醒未更新');

    @unlink($dbPath);
});
