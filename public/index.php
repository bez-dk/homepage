<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = t('home.title');
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1><?= e(t('home.heading')) ?></h1>
    <p><?= e(t('site.tagline')) ?></p>
    <a href="/contact" class="button"><?= e(t('home.cta')) ?></a>
</section>

<section class="cards">
    <?php foreach ([1, 2, 3] as $i): ?>
        <article class="card">
            <h2><?= e(t("home.card$i.title")) ?></h2>
            <p><?= e(t("home.card$i.text")) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
