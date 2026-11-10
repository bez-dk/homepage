<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = t('guides.title');
require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <header class="page-header">
        <h1><?= e(t('guides.title')) ?></h1>
        <p class="lead"><?= e(t('guides.lead')) ?></p>
    </header>

    <div class="cards cards-2">
        <?php foreach ($guideCategories as $category): ?>
            <article class="card" id="<?= e($category['id']) ?>">
                <span class="card-icon color-<?= e($category['color']) ?>"><?= icon($category['icon']) ?></span>
                <h2><?= e(tr($category['title'])) ?></h2>
                <p><?= e(tr($category['text'])) ?></p>
                <ul class="link-list">
                    <?php foreach ($category['guides'] as $guide): ?>
                        <li>
                            <?php if (isset($guide['url'])): ?>
                                <a href="<?= e($guide['url']) ?>"><?= icon('arrow-right') ?> <?= e(tr($guide)) ?></a>
                            <?php else: ?>
                                <span class="muted-link"><?= icon('arrow-right') ?> <?= e(tr($guide)) ?> <span class="badge badge-soft"><?= e(t('common.coming_soon')) ?></span></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
