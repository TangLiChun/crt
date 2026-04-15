<?php

declare(strict_types=1);

ob_start();
partial('marketing-posts/form', [
    'csrfToken' => $csrfToken,
    'post' => $post,
    'rules' => $rules,
    'action' => '/marketing-posts/' . $post['id'],
]);
$editPostDrawer = (string) ob_get_clean();
?>
<section class="panel-card detail-header">
    <div>
        <p class="eyebrow">排期详情</p>
        <h2><?= e((string) $post['title']) ?></h2>
        <p><?= e((string) $post['channel_name']) ?> · <?= e(format_datetime((string) $post['planned_at'])) ?></p>
    </div>
    <div class="detail-actions">
        <?php component('status-badge', ['label' => $post['status'] === 'published' ? '已发布' : '待发布', 'tone' => $post['status'] === 'published' ? 'success' : 'warning']); ?>
        <button class="button" type="button" data-drawer-open="edit-post-drawer">编辑排期</button>
    </div>
</section>

<section class="detail-grid single-column">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">内容概览</p>
                <h2>排期信息</h2>
            </div>
        </div>
        <dl class="info-grid">
            <div><dt>渠道</dt><dd><?= e((string) $post['channel_name']) ?></dd></div>
            <div><dt>计划时间</dt><dd><?= e(format_datetime((string) $post['planned_at'])) ?></dd></div>
            <div><dt>发布时间</dt><dd><?= e(format_datetime((string) ($post['published_at'] ?? ''), '尚未发布')) ?></dd></div>
            <div><dt>负责人</dt><dd><?= e((string) ($post['creator_name'] ?? '未分配')) ?></dd></div>
            <div><dt>间隔规则</dt><dd><?= e((string) $post['min_gap_hours']) ?> 小时</dd></div>
        </dl>
        <div class="notes-block">
            <h3>摘要</h3>
            <p><?= e((string) ($post['content'] ?: '暂无摘要')) ?></p>
        </div>
    </article>
</section>

<?php partial('components/drawer', [
    'id' => 'edit-post-drawer',
    'title' => '编辑排期',
    'description' => '调整日期后，记得再确认发帖间隔是否合理。',
    'content' => $editPostDrawer,
]); ?>
