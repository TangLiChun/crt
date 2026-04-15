<?php

declare(strict_types=1);

$contact = $contact ?? null;
$action = $action ?? '/contacts';
?>
<form class="form-grid" method="post" action="<?= e($action) ?>">
    <?= csrf_input((string) $csrfToken) ?>
    <div class="field-group">
        <label for="contact_name">联系人</label>
        <input id="contact_name" name="name" required value="<?= e((string) ($contact['name'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="contact_company">公司</label>
        <input id="contact_company" name="company_name" value="<?= e((string) ($contact['company_name'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="contact_type">类型</label>
        <select id="contact_type" name="contact_type">
            <option value="lead" <?= (($contact['contact_type'] ?? 'lead') === 'lead') ? 'selected' : '' ?>>潜在客户</option>
            <option value="customer" <?= (($contact['contact_type'] ?? '') === 'customer') ? 'selected' : '' ?>>现有客户</option>
        </select>
    </div>
    <div class="field-group">
        <label for="contact_stage">阶段</label>
        <input id="contact_stage" name="stage" value="<?= e((string) ($contact['stage'] ?? '新线索')) ?>">
    </div>
    <div class="field-group">
        <label for="contact_phone">电话</label>
        <input id="contact_phone" name="phone" value="<?= e((string) ($contact['phone'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="contact_email">邮箱</label>
        <input id="contact_email" name="email" value="<?= e((string) ($contact['email'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="contact_source">来源</label>
        <input id="contact_source" name="source" value="<?= e((string) ($contact['source'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="contact_next_follow_up">下次回访</label>
        <input id="contact_next_follow_up" type="datetime-local" name="next_follow_up_at" value="<?= e(isset($contact['next_follow_up_at']) && $contact['next_follow_up_at'] ? str_replace(' ', 'T', substr((string) $contact['next_follow_up_at'], 0, 16)) : '') ?>">
    </div>
    <div class="field-group full-span">
        <label for="contact_notes">备注</label>
        <textarea id="contact_notes" name="notes" rows="4"><?= e((string) ($contact['notes'] ?? '')) ?></textarea>
    </div>
    <div class="form-actions full-span">
        <button class="button" type="submit"><?= $contact ? '保存更新' : '创建客户' ?></button>
    </div>
</form>
