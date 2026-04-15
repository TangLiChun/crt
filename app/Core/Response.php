<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private function __construct(
        public readonly string $type,
        public readonly int $status = 200,
        public readonly array $headers = [],
        public readonly ?string $body = null,
        public readonly ?string $template = null,
        public readonly array $data = [],
        public readonly ?string $location = null,
    ) {
    }

    public static function view(string $template, array $data = [], int $status = 200): self
    {
        return new self('view', $status, [], null, $template, $data);
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self('json', $status, ['Content-Type' => 'application/json; charset=utf-8'], json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self('redirect', $status, [], null, null, [], $location);
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self('html', $status, ['Content-Type' => 'text/html; charset=utf-8'], $body);
    }
}
