<?php

declare(strict_types=1);
?>
<section class="summary-grid">
    <?php foreach ($stats as $item): ?>
        <?php component('summary-card', $item); ?>
    <?php endforeach; ?>
</section>

<section class="panel-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">KPI 快照</p>
            <h2><?= e((string) $kpiSnapshot['scopeLabel']) ?></h2>
        </div>
        <a class="button button-ghost" href="/kpi">查看完整看板</a>
    </div>
    <div class="summary-grid compact">
        <?php foreach ($kpiSnapshot['cards'] as $item): ?>
            <?php component('summary-card', $item); ?>
        <?php endforeach; ?>
    </div>
    <div class="stack-list">
        <?php foreach ($kpiSnapshot['highlights'] as $line): ?>
            <article class="stack-row">
                <div>
                    <strong><?= e((string) $kpiSnapshot['periodLabel']) ?></strong>
                    <p><?= e($line) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="dashboard-grid">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">今日重点</p>
                <h2>待回访与提醒</h2>
            </div>
            <a class="button button-ghost" href="/reminders">查看全部</a>
        </div>
        <?php if (empty($dueReminders)): ?>
            <div class="empty-card">
                <h3>今天没有待处理提醒</h3>
                <p>当前客户节奏比较健康，可以继续推进新线索。</p>
            </div>
        <?php else: ?>
            <div class="stack-list">
                <?php foreach ($dueReminders as $item): ?>
                    <article class="stack-row">
                        <div>
                            <strong><?= e((string) $item['title']) ?></strong>
                            <p><?= e((string) ($item['detail'] ?? '')) ?></p>
                        </div>
                        <?php component('status-badge', ['label' => $item['urgency'] === 'overdue' ? '已超期' : ($item['urgency'] === 'today' ? '今日处理' : '即将到期'), 'tone' => $item['urgency'] === 'overdue' ? 'danger' : 'warning']); ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">发帖节奏</p>
                <h2>营销排期风险</h2>
            </div>
            <a class="button button-ghost" href="/marketing-posts">进入排期</a>
        </div>
        <?php if (empty($postAlerts)): ?>
            <div class="empty-card">
                <h3>排期节奏正常</h3>
                <p>当前没有发帖过密或断更风险。</p>
            </div>
        <?php else: ?>
            <div class="stack-list">
                <?php foreach ($postAlerts as $alert): ?>
                    <?php component('reminder-banner', [
                        'title' => $alert['title'],
                        'message' => $alert['message'],
                        'tone' => $alert['tone'],
                        'actionLabel' => '定位排期',
                        'actionTarget' => 'post-' . $alert['related_post_id'],
                    ]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<section class="dashboard-grid">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">客户动态</p>
                <h2>最近更新的客户</h2>
            </div>
            <a class="button button-ghost" href="/contacts">客户列表</a>
        </div>
        <div class="stack-list">
            <?php foreach ($recentContacts as $contact): ?>
                <a class="stack-row stack-link" href="/contacts/<?= e((string) $contact['id']) ?>">
                    <div>
                        <strong><?= e((string) $contact['name']) ?></strong>
                        <p><?= e((string) ($contact['company_name'] ?: '未填写公司')) ?></p>
                    </div>
                    <div class="stack-meta">
                        <?php component('status-badge', ['label' => $contact['contact_type'] === 'lead' ? '潜在客户' : '现有客户', 'tone' => $contact['contact_type'] === 'lead' ? 'info' : 'success']); ?>
                        <small><?= e(format_datetime((string) $contact['updated_at'])) ?></small>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">近期计划</p>
                <h2>即将发布的内容</h2>
            </div>
        </div>
        <div class="stack-list">
            <?php foreach ($upcomingPosts as $post): ?>
                <article class="stack-row" id="post-<?= e((string) $post['id']) ?>">
                    <div>
                        <strong><?= e((string) $post['title']) ?></strong>
                        <p><?= e((string) $post['channel_name']) ?> · <?= e(format_datetime((string) $post['planned_at'])) ?></p>
                    </div>
                    <?php component('status-badge', ['label' => $post['status'] === 'published' ? '已发布' : '待发布', 'tone' => $post['status'] === 'published' ? 'success' : 'warning']); ?>
                </article>
            <?php endforeach; ?>
        </div>
    </article>
</section>
