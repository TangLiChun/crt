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

$GLOBALS['__tests'] = [];

function test(string $name, callable $callback): void
{
    $GLOBALS['__tests'][] = ['name' => $name, 'callback' => $callback];
}

function assert_true(bool $condition, string $message = '断言失败'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message !== '' ? $message : sprintf('断言失败，期望 %s，实际 %s', var_export($expected, true), var_export($actual, true)));
    }
}

function assert_contains(string $needle, string $haystack, string $message = ''): void
{
    if (!str_contains($haystack, $needle)) {
        throw new RuntimeException($message !== '' ? $message : sprintf('未找到预期内容: %s', $needle));
    }
}

function test_db_path(string $prefix = 'crm'): string
{
    return sys_get_temp_dir() . '/' . $prefix . '-' . uniqid('', true) . '.sqlite';
}

function boot_test_database(?string $path = null): array
{
    $dbPath = $path ?? test_db_path();
    putenv('CRM_DB_PATH=' . $dbPath);

    if (file_exists($dbPath)) {
        unlink($dbPath);
    }

    $dbConfig = require config_path('database.php');
    $appConfig = require config_path('app.php');
    $database = new App\Core\Database($dbConfig);
    $database->ensureInitialized();

    $users = new App\Domain\Repositories\UserRepository($database->pdo());
    foreach (($appConfig['demo_users'] ?? [$appConfig['demo_user']]) as $demoUser) {
        $users->ensure($demoUser);
    }

    return [$dbPath, $database->pdo()];
}

function make_app(?string $dbPath = null): App\Core\App
{
    if ($dbPath !== null) {
        putenv('CRM_DB_PATH=' . $dbPath);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
    }

    return new App\Core\App();
}
