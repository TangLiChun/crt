<?php

declare(strict_types=1);
?>
<article class="reminder-banner tone-<?= e((string) ($tone ?? 'neutral')) ?>">
    <div>
        <p class="banner-title"><?= e((string) ($title ?? '提醒')) ?></p>
        <p class="banner-text"><?= e((string) ($message ?? '')) ?></p>
    </div>
    <?php if (!empty($actionLabel ?? '') && !empty($actionTarget ?? '')): ?>
        <button class="button button-secondary" type="button" data-scroll-target="<?= e((string) $actionTarget) ?>">
            <?= e((string) $actionLabel) ?>
        </button>
    <?php endif; ?>
</article>
