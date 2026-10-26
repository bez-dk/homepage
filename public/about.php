<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = t('about.title');
require __DIR__ . '/includes/header.php';
?>

<h1><?= e(t('about.title')) ?></h1>
<p><?= e(t('about.text1')) ?></p>
<p><?= e(t('about.text2')) ?></p>

<?php require __DIR__ . '/includes/footer.php'; ?>
