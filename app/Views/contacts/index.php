<?php

declare(strict_types=1);

ob_start();
partial('contacts/form', [
    'csrfToken' => $csrfToken,
    'action' => '/contacts',
]);
$drawerContent = (string) ob_get_clean();
?>

<section class="summary-grid compact">
    <?php component('summary-card', ['label' => '客户总数', 'value' => $stats['total'], 'tone' => 'neutral']); ?>
    <?php component('summary-card', ['label' => '潜在客户', 'value' => $stats['leads'], 'tone' => 'info']); ?>
    <?php component('summary-card', ['label' => '现有客户', 'value' => $stats['customers'], 'tone' => 'success']); ?>
    <?php component('summary-card', ['label' => '待回访', 'value' => $stats['due_follow_ups'], 'tone' => 'warning']); ?>
</section>

<section class="panel-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">客户面板</p>
            <h2>客户与潜客列表</h2>
        </div>
        <button class="button" type="button" data-drawer-open="new-contact-drawer">快速新建</button>
    </div>

    <?php component('filter-bar', ['filters' => $filters]); ?>

    <div class="table-shell">
        <table class="data-table">
            <thead>
            <tr>
                <th>联系人</th>
                <th>公司 / 来源</th>
                <th>阶段</th>
                <th>负责人</th>
                <th>最近跟进</th>
                <th>下次回访</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($contacts)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-card">
                            <h3>没有匹配结果</h3>
                            <p>试试放宽关键词，或者直接创建新的客户档案。</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td>
                            <div class="table-primary">
                                <a href="/contacts/<?= e((string) $contact['id']) ?>"><?= e((string) $contact['name']) ?></a>
                                <?php component('status-badge', ['label' => $contact['contact_type'] === 'lead' ? '潜在客户' : '现有客户', 'tone' => $contact['contact_type'] === 'lead' ? 'info' : 'success']); ?>
                            </div>
                        </td>
                        <td>
                            <strong><?= e((string) ($contact['company_name'] ?: '未填写公司')) ?></strong>
                            <p><?= e((string) ($contact['source'] ?: '未标记来源')) ?></p>
                        </td>
                        <td><?php component('status-badge', ['label' => $contact['stage'], 'tone' => 'neutral']); ?></td>
                        <td><?= e((string) ($contact['owner_name'] ?: '未分配')) ?></td>
                        <td><?= e(format_datetime((string) ($contact['last_contacted_at'] ?? ''))) ?></td>
                        <td>
                            <?php
                            $hoursLeft = hours_from_now((string) ($contact['next_follow_up_at'] ?? ''));
                            $tone = ($hoursLeft !== null && $hoursLeft < 0) ? 'danger' : (($hoursLeft !== null && $hoursLeft < 24) ? 'warning' : 'success');
                            component('status-badge', ['label' => format_datetime((string) ($contact['next_follow_up_at'] ?? ''), '未设置'), 'tone' => $tone]);
                            ?>
                        </td>
                        <td><a class="button button-ghost" href="/contacts/<?= e((string) $contact['id']) ?>">查看详情</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php partial('components/drawer', [
    'id' => 'new-contact-drawer',
    'title' => '新建客户或潜客',
    'description' => '优先录入最关键的信息，先让销售流程跑起来。',
    'content' => $drawerContent,
]); ?>
