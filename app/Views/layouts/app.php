<?php

declare(strict_types=1);

$isLoginPage = ($currentPath ?? '') === '/login';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Sales Rhythm CRM') ?></title>
    <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
    <script defer src="<?= e(asset('app.js')) ?>"></script>
</head>
<body class="<?= $isLoginPage ? 'login-page' : 'app-page' ?>">
<?php if ($isLoginPage): ?>
    <main class="login-shell">
        <?= $content ?>
    </main>
<?php else: ?>
    <div class="app-shell">
        <?php partial('layouts/partials/nav', [
            'currentPath' => $currentPath ?? '/',
            'authUser' => $authUser ?? null,
            'csrfToken' => $csrfToken ?? '',
        ]); ?>
        <div class="app-main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">销售节奏面板</p>
                    <h1><?= e($pageTitle ?? 'Sales Rhythm CRM') ?></h1>
                </div>
                <div class="topbar-meta">
                    <span class="meta-pill">Asia/Shanghai</span>
                    <span class="meta-pill">PHP 8.5</span>
                </div>
            </header>

            <?php if (!empty($flash)): ?>
                <div class="flash flash-<?= e((string) $flash['level']) ?>">
                    <strong><?= e((string) strtoupper((string) $flash['level'])) ?></strong>
                    <span><?= e((string) $flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <main class="content-shell">
                <?= $content ?>
            </main>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
