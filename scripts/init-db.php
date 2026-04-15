<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Support/helpers.php';

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }

    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$dbConfig = require config_path('database.php');
$appConfig = require config_path('app.php');

$database = new App\Core\Database($dbConfig);
$database->ensureInitialized();

$users = new App\Domain\Repositories\UserRepository($database->pdo());
foreach (($appConfig['demo_users'] ?? [$appConfig['demo_user']]) as $demoUser) {
    $users->ensure($demoUser);
}

echo "数据库已初始化: " . $dbConfig['path'] . PHP_EOL;
