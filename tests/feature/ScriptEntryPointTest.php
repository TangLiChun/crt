<?php

declare(strict_types=1);

test('关键脚本入口文件存在', function (): void {
    foreach (['scripts/init-db.php', 'scripts/serve.php', 'scripts/test.php', 'scripts/reset-db.php'] as $file) {
        assert_true(file_exists(base_path($file)), '缺少脚本 ' . $file);
    }
});
