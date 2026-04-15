<?php

declare(strict_types=1);
?>
<div class="drawer" id="<?= e((string) $id) ?>" hidden>
    <div class="drawer-backdrop" data-drawer-close="<?= e((string) $id) ?>"></div>
    <div class="drawer-panel">
        <header class="drawer-header">
            <div>
                <p class="eyebrow"><?= e((string) ($eyebrow ?? '快速操作')) ?></p>
                <h3><?= e((string) $title) ?></h3>
                <?php if (!empty($description ?? '')): ?>
                    <p><?= e((string) $description) ?></p>
                <?php endif; ?>
            </div>
            <button class="button button-ghost" type="button" data-drawer-close="<?= e((string) $id) ?>">关闭</button>
        </header>
        <div class="drawer-body">
            <?= $content ?>
        </div>
    </div>
</div>
