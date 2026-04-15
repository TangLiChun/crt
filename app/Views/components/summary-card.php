<?php

declare(strict_types=1);
?>
<article class="summary-card tone-<?= e((string) ($tone ?? 'neutral')) ?>">
    <p class="summary-label"><?= e((string) ($label ?? '')) ?></p>
    <div class="summary-value"><?= e((string) ($value ?? '0')) ?></div>
    <?php if (!empty($hint ?? '')): ?>
        <p class="summary-hint"><?= e((string) $hint) ?></p>
    <?php endif; ?>
</article>
