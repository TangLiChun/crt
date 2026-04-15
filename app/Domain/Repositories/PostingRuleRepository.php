<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use PDO;

final class PostingRuleRepository
{
    public function __construct(private readonly PDO $pdo, private readonly array $defaults)
    {
    }

    public function allActive(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM posting_rules WHERE is_active = 1 ORDER BY channel_name ASC');

        return $stmt->fetchAll();
    }

    public function findByChannel(string $channelName): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posting_rules WHERE channel_name = :channel_name LIMIT 1');
        $stmt->execute(['channel_name' => $channelName]);

        return $stmt->fetch() ?: null;
    }

    public function defaults(): array
    {
        return $this->defaults;
    }
}
