<?php

declare(strict_types=1);
?>
<section class="panel-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">提醒中心</p>
            <h2>待处理回访与发帖风险</h2>
        </div>
    </div>

    <?php if (!empty($postAlerts)): ?>
        <div class="stack-list banners">
            <?php foreach ($postAlerts as $alert): ?>
                <?php component('reminder-banner', [
                    'title' => $alert['title'],
                    'message' => $alert['message'],
                    'tone' => $alert['tone'],
                ]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="stack-list">
        <?php if (empty($reminders)): ?>
            <div class="empty-card">
                <h3>当前没有开放提醒</h3>
                <p>你可以回到客户详情页补跟进，或者去排期页继续规划营销内容。</p>
            </div>
        <?php else: ?>
            <?php foreach ($reminders as $reminder): ?>
                <article class="reminder-row">
                    <div>
                        <div class="inline-row">
                            <strong><?= e((string) $reminder['title']) ?></strong>
                            <?php component('status-badge', [
                                'label' => $reminder['urgency'] === 'overdue' ? '已超期' : ($reminder['urgency'] === 'today' ? '今日处理' : '即将到期'),
                                'tone' => $reminder['urgency'] === 'overdue' ? 'danger' : 'warning',
                            ]); ?>
                        </div>
                        <p><?= e((string) ($reminder['detail'] ?: '无额外说明')) ?></p>
                        <small>
                            到期时间：<?= e(format_datetime((string) $reminder['due_at'])) ?>
                            <?php if (is_manager_role((string) ($authUser['role'] ?? ''))): ?>
                                · 负责人：<?= e((string) ($reminder['assigned_user_name'] ?? '未分配')) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="inline-actions">
                        <form method="post" action="/reminders/<?= e((string) $reminder['id']) ?>/complete">
                            <?= csrf_input((string) $csrfToken) ?>
                            <button class="button button-secondary" type="submit">完成</button>
                        </form>
                        <form method="post" action="/reminders/<?= e((string) $reminder['id']) ?>/dismiss">
                            <?= csrf_input((string) $csrfToken) ?>
                            <button class="button button-ghost" type="submit">忽略</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
