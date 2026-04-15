<?php

declare(strict_types=1);

ob_start();
partial('contacts/form', [
    'csrfToken' => $csrfToken,
    'contact' => $contact,
    'action' => '/contacts/' . $contact['id'],
]);
$editDrawer = (string) ob_get_clean();
?>
<section class="detail-header panel-card">
    <div>
        <p class="eyebrow">客户详情</p>
        <h2><?= e((string) $contact['name']) ?></h2>
        <p><?= e((string) ($contact['company_name'] ?: '未填写公司')) ?> · <?= e((string) ($contact['owner_name'] ?: '未分配负责人')) ?></p>
    </div>
    <div class="detail-actions">
        <?php component('status-badge', ['label' => $contact['stage'], 'tone' => 'neutral']); ?>
        <?php component('status-badge', ['label' => $contact['contact_type'] === 'lead' ? '潜在客户' : '现有客户', 'tone' => $contact['contact_type'] === 'lead' ? 'info' : 'success']); ?>
        <button class="button" type="button" data-drawer-open="edit-contact-drawer">编辑资料</button>
    </div>
</section>

<section class="detail-grid">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">基础信息</p>
                <h2>客户摘要</h2>
            </div>
        </div>
        <dl class="info-grid">
            <div><dt>电话</dt><dd><?= e((string) ($contact['phone'] ?: '未填写')) ?></dd></div>
            <div><dt>邮箱</dt><dd><?= e((string) ($contact['email'] ?: '未填写')) ?></dd></div>
            <div><dt>来源</dt><dd><?= e((string) ($contact['source'] ?: '未填写')) ?></dd></div>
            <div><dt>下次回访</dt><dd><?= e(format_datetime((string) ($contact['next_follow_up_at'] ?? ''), '未设置')) ?></dd></div>
        </dl>
        <div class="notes-block">
            <h3>备注</h3>
            <p><?= e((string) ($contact['notes'] ?: '暂无备注')) ?></p>
        </div>

        <div class="follow-up-box">
            <h3>快速补一条跟进</h3>
            <form class="form-grid compact-form" method="post" action="/contacts/<?= e((string) $contact['id']) ?>/follow-ups">
                <?= csrf_input((string) $csrfToken) ?>
                <div class="field-group full-span">
                    <label for="follow_up_content">跟进内容</label>
                    <textarea id="follow_up_content" name="content" rows="4" required placeholder="记录本次沟通的核心结论、阻碍和下一步动作"></textarea>
                </div>
                <div class="field-group">
                    <label for="follow_up_outcome">结果标签</label>
                    <input id="follow_up_outcome" name="outcome" placeholder="如：待报价 / 已约演示">
                </div>
                <div class="field-group">
                    <label for="follow_up_next">下次回访</label>
                    <input id="follow_up_next" type="datetime-local" name="next_follow_up_at">
                </div>
                <div class="form-actions full-span">
                    <button class="button" type="submit">保存跟进</button>
                </div>
            </form>
        </div>
    </article>

    <article class="panel-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">沟通链路</p>
                <h2>跟进时间轴</h2>
            </div>
            <span class="meta-pill"><?= e((string) $activitySummary['total']) ?> 条记录</span>
        </div>
        <?php partial('contacts/_timeline', ['followUps' => $followUps]); ?>
    </article>
</section>

<?php partial('components/drawer', [
    'id' => 'edit-contact-drawer',
    'title' => '编辑客户信息',
    'description' => '调整阶段、回访时间和联系方式，让团队信息保持统一。',
    'content' => $editDrawer,
]); ?>
