<?php

declare(strict_types=1);
?>
<section class="panel-card scope-banner">
    <div>
        <p class="eyebrow">经营视角</p>
        <h2><?= e((string) $scopeLabel) ?></h2>
        <p><?= e((string) $periodLabel) ?> 内的客户推进、回访执行和营销节奏会统一汇总在这里。</p>
    </div>
    <div class="topbar-meta">
        <span class="meta-pill"><?= e(role_label((string) ($authUser['role'] ?? 'sales'))) ?></span>
        <span class="meta-pill"><?= e((string) $periodLabel) ?></span>
    </div>
</section>

<section class="summary-grid compact">
    <?php foreach ($cards as $item): ?>
        <?php component('summary-card', $item); ?>
    <?php endforeach; ?>
</section>

<section class="dashboard-grid">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">核心提示</p>
                <h2>本周期重点</h2>
            </div>
        </div>
        <div class="stack-list">
            <?php foreach ($highlights as $line): ?>
                <article class="stack-row">
                    <div>
                        <strong><?= e((string) $periodLabel) ?></strong>
                        <p><?= e($line) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">执行压力</p>
                <h2>本周最需要处理的事</h2>
            </div>
        </div>
        <div class="stack-list">
            <?php foreach ($executionRows as $item): ?>
                <article class="metric-row">
                    <div>
                        <strong><?= e((string) $item['label']) ?></strong>
                        <p><?= e((string) $item['note']) ?></p>
                    </div>
                    <?php component('status-badge', ['label' => (string) $item['value'], 'tone' => $item['tone']]); ?>
                </article>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="dashboard-grid">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">客户阶段</p>
                <h2>客户池结构</h2>
            </div>
            <span class="meta-pill"><?= e((string) $contactStats['total']) ?> 个档案</span>
        </div>
        <?php if (empty($pipeline)): ?>
            <div class="empty-card">
                <h3>暂无阶段数据</h3>
                <p>录入客户或潜客后，这里会自动形成阶段分布。</p>
            </div>
        <?php else: ?>
            <div class="progress-list">
                <?php foreach ($pipeline as $item): ?>
                    <article class="progress-row">
                        <div class="inline-row">
                            <strong><?= e((string) $item['label']) ?></strong>
                            <span><?= e((string) $item['count']) ?></span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar" style="width: <?= e((string) $item['width']) ?>%;"></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">提醒与排期</p>
                <h2>节奏健康度</h2>
            </div>
        </div>
        <div class="stack-list">
            <article class="metric-row">
                <div>
                    <strong>开放提醒</strong>
                    <p>当前仍在等待处理的客户或营销提醒总数。</p>
                </div>
                <?php component('status-badge', ['label' => (string) $reminderStats['total'], 'tone' => $reminderStats['total'] > 0 ? 'warning' : 'success']); ?>
            </article>
            <article class="metric-row">
                <div>
                    <strong>已计划内容</strong>
                    <p><?= e((string) $periodLabel) ?> 内已录入的营销排期。</p>
                </div>
                <?php component('status-badge', ['label' => (string) $postStats['planned_count'], 'tone' => 'info']); ?>
            </article>
            <article class="metric-row">
                <div>
                    <strong>已发布内容</strong>
                    <p>已切换为发布状态的内容数量。</p>
                </div>
                <?php component('status-badge', ['label' => (string) $postStats['published_count'], 'tone' => 'success']); ?>
            </article>
            <article class="metric-row">
                <div>
                    <strong>近 30 天跟进</strong>
                    <p>本周期被记录进系统的客户跟进次数。</p>
                </div>
                <?php component('status-badge', ['label' => (string) $followUpCount, 'tone' => 'info']); ?>
            </article>
        </div>
    </article>
</section>

<section class="panel-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">成员执行</p>
            <h2><?= is_manager_role((string) ($authUser['role'] ?? '')) ? '团队成员排行' : '我的 KPI 结构' ?></h2>
        </div>
    </div>
    <div class="table-shell">
        <table class="data-table leaderboard-table">
            <thead>
            <tr>
                <th>成员</th>
                <th>客户池</th>
                <th>转化率</th>
                <th>近 30 天跟进</th>
                <th>已发布内容</th>
                <th>待跟进</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($leaderboard as $member): ?>
                <tr>
                    <td>
                        <strong><?= e((string) $member['display_name']) ?></strong>
                        <p><?= e(role_label((string) $member['role'])) ?></p>
                    </td>
                    <td><?= e((string) $member['total_contacts']) ?> 个</td>
                    <td><?php component('status-badge', ['label' => $member['conversion_rate'] . '%', 'tone' => $member['conversion_rate'] >= 45 ? 'success' : 'info']); ?></td>
                    <td><?= e((string) $member['follow_up_count']) ?></td>
                    <td><?= e((string) $member['published_count']) ?> / <?= e((string) $member['planned_count']) ?></td>
                    <td><?= e((string) $member['due_follow_ups']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
