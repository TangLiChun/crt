<?php

declare(strict_types=1);

$demoUsers = $demoUsers ?? [$demoUser];
?>
<section class="login-card">
    <div class="login-copy">
        <p class="eyebrow">Sales Rhythm CRM</p>
        <h1>把客户跟进和发帖节奏放在同一张工作台</h1>
        <p>登录后你可以管理现有客户、潜在客户、回访提醒，以及营销帖排期与间隔风险。</p>
        <p class="eyebrow">演示账号</p>
        <div class="account-list">
            <?php foreach ($demoUsers as $account): ?>
                <div class="demo-note">
                    <strong><?= e(role_label((string) ($account['role'] ?? 'sales'))) ?></strong>
                    <span><?= e((string) $account['username']) ?> / <?= e((string) $account['password']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <form class="panel-card login-form" method="post" action="/login">
        <?= csrf_input((string) $csrfToken) ?>
        <div class="field-group">
            <label for="username">账号</label>
            <input id="username" name="username" type="text" value="<?= e((string) $demoUser['username']) ?>" required>
        </div>
        <div class="field-group">
            <label for="password">密码</label>
            <input id="password" name="password" type="password" value="<?= e((string) $demoUser['password']) ?>" required>
        </div>
        <button class="button" type="submit">进入工作台</button>
    </form>
</section>
