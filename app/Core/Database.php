<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class Database
{
    private ?PDO $pdo = null;

    public function __construct(private readonly array $config)
    {
    }

    public function path(): string
    {
        return $this->config['path'];
    }

    public function ensureInitialized(): void
    {
        $dbPath = $this->path();
        $dbDir = dirname($dbPath);

        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $isNew = !file_exists($dbPath);
        $this->pdo();

        if ($isNew || !$this->hasTable('users')) {
            $schema = base_path('database/schema.sql');
            $seed = base_path('database/seed.sql');

            if (!file_exists($schema)) {
                throw new RuntimeException('缺少 database/schema.sql');
            }

            $this->executeSqlFile($schema);

            if (file_exists($seed)) {
                $this->executeSqlFile($seed);
            }
        }
    }

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $this->pdo = new PDO('sqlite:' . $this->path());
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->applyPragmas($this->pdo);

        return $this->pdo;
    }

    public function applyPragmas(PDO $pdo): void
    {
        if (($this->config['foreign_keys'] ?? false) === true) {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        $journalMode = (string) ($this->config['journal_mode'] ?? 'WAL');
        $pdo->exec('PRAGMA journal_mode = ' . $journalMode);
        $pdo->exec('PRAGMA busy_timeout = ' . (int) ($this->config['busy_timeout'] ?? 5000));
    }

    public function executeSqlFile(string $file): void
    {
        $sql = trim((string) file_get_contents($file));

        if ($sql !== '') {
            $this->pdo()->exec($sql);
        }
    }

    private function hasTable(string $table): bool
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM sqlite_master WHERE type = :type AND name = :name'
        );
        $stmt->execute([
            'type' => 'table',
            'name' => $table,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
