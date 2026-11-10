<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(404);
$pageTitle = t('notfound.title');
require __DIR__ . '/includes/header.php';
?>

<section class="hero hero-small">
    <div class="container">
        <p class="eyebrow">404</p>
        <h1 class="hero-title"><?= e(t('notfound.title')) ?></h1>
        <p class="hero-lead"><?= e(t('notfound.text')) ?></p>
        <div class="hero-actions">
            <a href="/" class="button button-primary"><?= e(t('notfound.back')) ?></a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
