<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = t('projects.title');
require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <header class="page-header">
        <h1><?= e(t('projects.title')) ?></h1>
        <p class="lead"><?= e(t('projects.lead')) ?></p>
    </header>

    <div class="cards cards-2">
        <article class="card">
            <span class="card-icon color-orange"><?= icon('folder') ?></span>
            <h2><?= e(t('projects.covers')) ?></h2>
            <ul class="check-list">
                <?php foreach (range(1, 6) as $i): ?>
                    <li><?= icon('check') ?> <?= e(t("projects.cover$i")) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="card card-empty">
            <span class="card-icon color-violet"><?= icon('sparkles') ?></span>
            <h2><?= e(t('projects.empty_title')) ?></h2>
            <p><?= e(t('projects.empty_text')) ?></p>
            <span class="badge badge-soft"><?= e(t('common.coming_soon')) ?></span>
        </article>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
