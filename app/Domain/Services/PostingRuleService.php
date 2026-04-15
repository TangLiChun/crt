<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Repositories\PostingRuleRepository;

final class PostingRuleService
{
    public function __construct(private readonly PostingRuleRepository $rules)
    {
    }

    public function all(): array
    {
        return $this->rules->allActive();
    }

    public function forChannel(string $channel): array
    {
        return $this->rules->findByChannel($channel) ?? [
            'channel_name' => $channel,
            'min_gap_hours' => $this->rules->defaults()['default_min_gap_hours'] ?? 72,
            'max_gap_hours' => $this->rules->defaults()['default_stale_gap_hours'] ?? 168,
        ];
    }
}
