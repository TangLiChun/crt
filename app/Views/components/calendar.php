<?php

declare(strict_types=1);
?>
<div class="calendar-shell" data-calendar-shell>
    <?php if (empty($calendar)): ?>
        <div class="empty-card">
            <h3>还没有排期</h3>
            <p>先创建第一条营销帖子计划，系统就会开始帮你盯发帖节奏。</p>
        </div>
    <?php else: ?>
        <div class="view-toggle">
            <button class="button button-secondary is-selected" type="button" data-view-toggle="month">月视图</button>
            <button class="button button-ghost" type="button" data-view-toggle="week">周视图</button>
        </div>
        <div class="calendar-grid" data-calendar-view="month">
            <?php foreach ($calendar as $day => $dayPosts): ?>
                <section class="calendar-day">
                    <header>
                        <span><?= e($day) ?></span>
                        <strong><?= count($dayPosts) ?> 条</strong>
                    </header>
                    <div class="calendar-list">
                        <?php foreach ($dayPosts as $post): ?>
                            <article class="calendar-post">
                                <p><?= e((string) $post['title']) ?></p>
                                <span><?= e((string) $post['channel_name']) ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
        <div class="calendar-week" data-calendar-view="week" hidden>
            <?php foreach ($calendar as $day => $dayPosts): ?>
                <article class="week-row">
                    <div class="week-date"><?= e($day) ?></div>
                    <div class="week-posts">
                        <?php foreach ($dayPosts as $post): ?>
                            <div class="week-pill">
                                <strong><?= e((string) $post['channel_name']) ?></strong>
                                <span><?= e((string) $post['title']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
