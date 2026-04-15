<?php

declare(strict_types=1);

$post = $post ?? null;
$action = $action ?? '/marketing-posts';
?>
<form class="form-grid" method="post" action="<?= e($action) ?>">
    <?= csrf_input((string) $csrfToken) ?>
    <div class="field-group">
        <label for="post_title">标题</label>
        <input id="post_title" name="title" required value="<?= e((string) ($post['title'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="post_channel">渠道</label>
        <select id="post_channel" name="channel_name">
            <?php foreach ($rules as $rule): ?>
                <option value="<?= e((string) $rule['channel_name']) ?>" <?= (($post['channel_name'] ?? '') === $rule['channel_name']) ? 'selected' : '' ?>>
                    <?= e((string) $rule['channel_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field-group">
        <label for="post_planned">计划发布时间</label>
        <input id="post_planned" type="datetime-local" name="planned_at" required value="<?= e(isset($post['planned_at']) && $post['planned_at'] ? str_replace(' ', 'T', substr((string) $post['planned_at'], 0, 16)) : '') ?>">
    </div>
    <div class="field-group">
        <label for="post_status">状态</label>
        <select id="post_status" name="status">
            <?php foreach (['planned' => '待发布', 'published' => '已发布', 'paused' => '已暂停'] as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= (($post['status'] ?? 'planned') === $key) ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field-group full-span">
        <label for="post_content">内容摘要</label>
        <textarea id="post_content" name="content" rows="4"><?= e((string) ($post['content'] ?? '')) ?></textarea>
    </div>
    <div class="form-actions full-span">
        <button class="button" type="submit"><?= $post ? '保存排期' : '创建排期' ?></button>
    </div>
</form>
