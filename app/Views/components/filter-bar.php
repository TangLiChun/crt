<?php

declare(strict_types=1);
?>
<form class="filter-bar" method="get" action="/contacts">
    <div class="field-group grow">
        <label for="contacts-q">关键词</label>
        <input id="contacts-q" type="search" name="q" placeholder="搜索姓名 / 公司 / 电话" value="<?= e((string) ($filters['q'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="contact_type">类型</label>
        <select id="contact_type" name="contact_type">
            <option value="">全部</option>
            <option value="lead" <?= (($filters['contact_type'] ?? '') === 'lead') ? 'selected' : '' ?>>潜在客户</option>
            <option value="customer" <?= (($filters['contact_type'] ?? '') === 'customer') ? 'selected' : '' ?>>现有客户</option>
        </select>
    </div>
    <div class="field-group">
        <label for="stage">阶段</label>
        <input id="stage" type="text" name="stage" placeholder="如：已跟进" value="<?= e((string) ($filters['stage'] ?? '')) ?>">
    </div>
    <div class="field-group">
        <label for="follow_up">回访状态</label>
        <select id="follow_up" name="follow_up">
            <option value="">全部</option>
            <option value="due" <?= (($filters['follow_up'] ?? '') === 'due') ? 'selected' : '' ?>>仅看待回访</option>
        </select>
    </div>
    <div class="filter-actions">
        <button class="button button-secondary" type="submit">筛选</button>
        <a class="button button-ghost" href="/contacts">重置</a>
    </div>
</form>
