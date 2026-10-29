<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = t('tools.title');
require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <header class="page-header">
        <h1><?= e(t('tools.title')) ?></h1>
        <p class="lead"><?= e(t('tools.lead')) ?></p>
    </header>

    <?php foreach ($toolCategories as $categoryId => $categoryName): ?>
        <section class="section-compact" id="<?= e($categoryId) ?>">
            <h2 class="category-title"><?= e(tr($categoryName)) ?></h2>
            <div class="tiles tiles-large">
                <?php foreach ($tools as $tool): ?>
                    <?php if ($tool['category'] === $categoryId): ?>
                        <?= tool_tile($tool, true) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
