<?php

declare(strict_types=1);
?>
<div class="timeline">
    <?php if (empty($followUps)): ?>
        <div class="empty-card">
            <h3>还没有跟进记录</h3>
            <p>先补一条最新沟通，让团队成员能看到完整上下文。</p>
        </div>
    <?php else: ?>
        <?php foreach ($followUps as $item): ?>
            <article class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-card">
                    <div class="timeline-meta">
                        <strong><?= e((string) ($item['user_name'] ?? '销售成员')) ?></strong>
                        <span><?= e(format_datetime((string) $item['created_at'])) ?></span>
                    </div>
                    <p><?= e((string) $item['content']) ?></p>
                    <div class="timeline-tags">
                        <?php if (!empty($item['outcome'])): ?>
                            <?php component('status-badge', ['label' => $item['outcome'], 'tone' => 'info']); ?>
                        <?php endif; ?>
                        <?php if (!empty($item['next_follow_up_at'])): ?>
                            <?php component('status-badge', ['label' => '下次回访 ' . format_datetime((string) $item['next_follow_up_at']), 'tone' => 'warning']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
