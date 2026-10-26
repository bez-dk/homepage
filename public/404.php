<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(404);
$pageTitle = t('notfound.title');
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1><?= e(t('notfound.title')) ?></h1>
    <p><?= e(t('notfound.text')) ?></p>
    <a href="/" class="button"><?= e(t('notfound.back')) ?></a>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
