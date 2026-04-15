<?php

declare(strict_types=1);

$host = $argv[1] ?? '127.0.0.1:8080';
$command = sprintf(
    '%s -S %s -t %s %s',
    escapeshellarg(PHP_BINARY),
    escapeshellarg($host),
    escapeshellarg(dirname(__DIR__) . '/public'),
    escapeshellarg(dirname(__DIR__) . '/public/index.php')
);

passthru($command, $code);
exit($code);
