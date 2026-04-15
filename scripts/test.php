<?php

declare(strict_types=1);

$command = sprintf('%s %s', escapeshellarg(PHP_BINARY), escapeshellarg(dirname(__DIR__) . '/tests/Runner.php'));
passthru($command, $code);
exit($code);
