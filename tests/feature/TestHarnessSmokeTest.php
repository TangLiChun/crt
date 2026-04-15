<?php

declare(strict_types=1);

test('测试运行器可以加载当前用例集', function (): void {
    assert_true(count($GLOBALS['__tests']) >= 1, '运行器没有加载测试');
});
