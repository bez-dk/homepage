<?php
require __DIR__ . '/includes/contact-handler.php';
$pageTitle = t('contact.title');
require __DIR__ . '/includes/header.php';
?>

<div class="container">
<header class="page-header">
    <h1><?= e(t('contact.title')) ?></h1>
    <p class="lead"><?= e(t('contact.lead')) ?></p>
</header>

<div class="card form-card">

<?php if ($sent): ?>
    <p class="notice success"><?= e(t('contact.sent')) ?></p>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="notice error">
        <?php foreach ($errors as $error): ?>
            <p><?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" action="/contact" class="contact-form">
    <label>
        <?= e(t('contact.name')) ?>
        <input type="text" name="name" value="<?= e($name) ?>" required>
    </label>
    <label>
        <?= e(t('contact.email')) ?>
        <input type="email" name="email" value="<?= e($email) ?>" required>
    </label>
    <label>
        <?= e(t('contact.message')) ?>
        <textarea name="message" rows="6" required><?= e($message) ?></textarea>
    </label>
    <div class="hp" aria-hidden="true">
        <label><?= e(t('contact.honeypot')) ?> <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>
    <button type="submit" class="button button-primary"><?= e(t('contact.send')) ?></button>
</form>
</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
