<?php

declare(strict_types=1);

ob_start();
partial('marketing-posts/form', [
    'csrfToken' => $csrfToken,
    'rules' => $rules,
    'action' => '/marketing-posts',
]);
$newPostDrawer = (string) ob_get_clean();
?>
<section class="panel-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">营销节奏</p>
            <h2>帖子排期与间隔提醒</h2>
        </div>
        <button class="button" type="button" data-drawer-open="new-post-drawer">新增排期</button>
    </div>

    <div class="stack-list banners">
        <?php if (empty($alerts)): ?>
            <?php component('reminder-banner', [
                'title' => '当前节奏健康',
                'message' => '没有检测到过密或断更风险，可以继续按照当前计划推进。',
                'tone' => 'success',
            ]); ?>
        <?php else: ?>
            <?php foreach ($alerts as $alert): ?>
                <?php component('reminder-banner', [
                    'title' => $alert['title'],
                    'message' => $alert['message'],
                    'tone' => $alert['tone'],
                    'actionLabel' => '定位',
                    'actionTarget' => 'post-row-' . $alert['related_post_id'],
                ]); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php component('calendar', ['calendar' => $calendar]); ?>

    <div class="table-shell">
        <table class="data-table">
            <thead>
            <tr>
                <th>标题</th>
                <th>渠道</th>
                <th>计划时间</th>
                <th>状态</th>
                <th>最小间隔</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $post): ?>
                <tr id="post-row-<?= e((string) $post['id']) ?>">
                    <td>
                        <strong><?= e((string) $post['title']) ?></strong>
                        <p><?= e((string) ($post['content'] ?: '无摘要')) ?></p>
                        <?php if (is_manager_role((string) ($authUser['role'] ?? ''))): ?>
                            <small>负责人：<?= e((string) ($post['creator_name'] ?? '未分配')) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= e((string) $post['channel_name']) ?></td>
                    <td><?= e(format_datetime((string) $post['planned_at'])) ?></td>
                    <td><?php component('status-badge', ['label' => $post['status'] === 'published' ? '已发布' : ($post['status'] === 'paused' ? '已暂停' : '待发布'), 'tone' => $post['status'] === 'published' ? 'success' : 'warning']); ?></td>
                    <td><?= e((string) $post['min_gap_hours']) ?> 小时</td>
                    <td><a class="button button-ghost" href="/marketing-posts/<?= e((string) $post['id']) ?>">详情</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php partial('components/drawer', [
    'id' => 'new-post-drawer',
    'title' => '新增营销排期',
    'description' => '系统会根据渠道规则自动判断是否过密或断更。',
    'content' => $newPostDrawer,
]); ?>
