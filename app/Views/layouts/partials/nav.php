<?php

declare(strict_types=1);

$items = [
    ['label' => '工作台', 'path' => '/'],
    ['label' => 'KPI 看板', 'path' => '/kpi'],
    ['label' => '客户与潜客', 'path' => '/contacts'],
    ['label' => '提醒中心', 'path' => '/reminders'],
    ['label' => '营销排期', 'path' => '/marketing-posts'],
];
?>
<aside class="sidebar">
    <div class="brand-panel">
        <p class="brand-kicker">Sales Rhythm</p>
        <h2>CRM 工作台</h2>
        <p>把客户跟进、回访和发帖节奏放进同一条工作流。</p>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($items as $item): ?>
            <a
                class="nav-link <?= ($currentPath === $item['path']) ? 'is-active' : '' ?>"
                href="<?= e($item['path']) ?>"
            >
                <?= e($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div>
            <p class="sidebar-user"><?= e((string) ($authUser['display_name'] ?? '未登录')) ?></p>
            <p class="sidebar-role"><?= e(role_label((string) ($authUser['role'] ?? ''))) ?></p>
        </div>
        <form method="post" action="/logout">
            <?= csrf_input((string) $csrfToken) ?>
            <button class="button button-ghost full-width" type="submit">退出登录</button>
        </form>
    </div>
</aside>
