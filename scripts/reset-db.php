<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Support/helpers.php';

$config = require config_path('database.php');
$path = $config['path'];

if (file_exists($path)) {
    unlink($path);
}

passthru(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/init-db.php'), $code);
exit($code);
