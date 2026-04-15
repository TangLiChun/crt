<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Support/helpers.php';

$config = require config_path('database.php');
$source = $config['path'];

if (!file_exists($source)) {
    fwrite(STDERR, "数据库文件不存在: {$source}" . PHP_EOL);
    exit(1);
}

if (!is_dir(storage_path('backups'))) {
    mkdir(storage_path('backups'), 0777, true);
}

$target = storage_path('backups/crm-' . date('Ymd-His') . '.sqlite');
copy($source, $target);

echo "数据库已备份到: {$target}" . PHP_EOL;
