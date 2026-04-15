<?php

declare(strict_types=1);

use App\Domain\Repositories\PostingRuleRepository;
use App\Domain\Services\PostingRuleService;

test('PostingRuleService 能读取渠道规则', function (): void {
    [$dbPath, $pdo] = boot_test_database();
    $service = new PostingRuleService(new PostingRuleRepository($pdo, [
        'default_min_gap_hours' => 72,
        'default_stale_gap_hours' => 168,
    ]));

    $rule = $service->forChannel('微信公众号');
    assert_same(72, (int) $rule['min_gap_hours']);

    @unlink($dbPath);
});
