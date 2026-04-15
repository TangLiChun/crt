<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$files = [];
foreach (['Unit', 'Feature'] as $suite) {
    $dir = __DIR__ . '/' . $suite;
    if (!is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);

foreach ($files as $file) {
    require $file;
}

$passed = 0;
$failed = 0;

foreach ($GLOBALS['__tests'] as $test) {
    try {
        $test['callback']();
        $passed++;
        echo "PASS {$test['name']}" . PHP_EOL;
    } catch (Throwable $exception) {
        $failed++;
        echo "FAIL {$test['name']}: {$exception->getMessage()}" . PHP_EOL;
    }
}

echo sprintf('Tests: %d passed, %d failed', $passed, $failed) . PHP_EOL;
exit($failed > 0 ? 1 : 0);
