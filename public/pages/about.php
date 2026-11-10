<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = t('about.title');
require __DIR__ . '/includes/header.php';

$values = [
    ['icon' => 'wrench', 'color' => 'teal'],
    ['icon' => 'book', 'color' => 'blue'],
    ['icon' => 'zap', 'color' => 'orange'],
];
?>

<div class="container">
    <header class="page-header">
        <h1><?= e(t('about.title')) ?></h1>
        <p class="lead"><?= e(t('about.lead')) ?></p>
    </header>

    <div class="prose">
        <p><?= e(t('about.text1')) ?></p>
        <p><?= e(t('about.text2')) ?></p>
    </div>

    <div class="cards section-compact">
        <?php foreach ($values as $i => $value): ?>
            <article class="card">
                <span class="card-icon color-<?= e($value['color']) ?>"><?= icon($value['icon']) ?></span>
                <h2><?= e(t('about.value' . ($i + 1) . '_title')) ?></h2>
                <p><?= e(t('about.value' . ($i + 1) . '_text')) ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="callout">
        <p><?= e(t('about.contact')) ?></p>
        <a href="/contact" class="button button-primary"><?= icon('mail') ?> <?= e(t('about.contact_cta')) ?></a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
