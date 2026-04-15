<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);

        return $path === '' ? $root : $root . '/' . ltrim($path, '/');
    }
}

if (!function_exists('view_path')) {
    function view_path(string $path = ''): string
    {
        return base_path('app/Views' . ($path === '' ? '' : '/' . ltrim($path, '/')));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path === '' ? '' : '/' . ltrim($path, '/')));
    }
}

if (!function_exists('database_path')) {
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path === '' ? '' : '/' . ltrim($path, '/')));
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path === '' ? '' : '/' . ltrim($path, '/')));
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('array_get')) {
    function array_get(array $source, string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $default;
        }

        $segments = explode('.', $key);
        $value = $source;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('component')) {
    function component(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include view_path('components/' . $view . '.php');
    }
}

if (!function_exists('partial')) {
    function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include view_path($view . '.php');
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $value, string $fallback = '未设置'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        try {
            return (new DateTimeImmutable($value))->setTimezone(new DateTimeZone('Asia/Shanghai'))->format('Y-m-d H:i');
        } catch (Throwable) {
            return $fallback;
        }
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $value, string $fallback = '未设置'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        try {
            return (new DateTimeImmutable($value))->setTimezone(new DateTimeZone('Asia/Shanghai'))->format('Y-m-d');
        } catch (Throwable) {
            return $fallback;
        }
    }
}

if (!function_exists('hours_from_now')) {
    function hours_from_now(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $target = new DateTimeImmutable($value, new DateTimeZone('Asia/Shanghai'));
        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Shanghai'));

        return (int) floor(($target->getTimestamp() - $now->getTimestamp()) / 3600);
    }
}

if (!function_exists('status_tone')) {
    function status_tone(string $value): string
    {
        return match ($value) {
            '已成交', '正常', 'safe', 'published', 'completed', 'done' => 'success',
            '超期', 'overdue', 'dense', 'warning', '待处理', 'open' => 'warning',
            'danger', 'stale', '冲突', 'dismissed', 'lost' => 'danger',
            default => 'neutral',
        };
    }
}

if (!function_exists('is_manager_role')) {
    function is_manager_role(?string $role): bool
    {
        return $role === 'manager';
    }
}

if (!function_exists('role_label')) {
    function role_label(?string $role): string
    {
        return match ($role) {
            'manager' => '管理层',
            'sales' => '销售',
            default => '成员',
        };
    }
}

if (!function_exists('percentage')) {
    function percentage(int|float $part, int|float $whole): int
    {
        if ($whole <= 0) {
            return 0;
        }

        return (int) round(($part / $whole) * 100);
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input(string $token): string
    {
        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}
