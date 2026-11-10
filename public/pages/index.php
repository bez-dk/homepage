<?php
require_once __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';

$explore = [
    [
        'icon'  => 'book',
        'color' => 'blue',
        'title' => t('home.guides_heading'),
        'text'  => t('home.guides_text'),
        'links' => array_map(fn ($c) => ['/guides#' . $c['id'], tr($c['title'])], array_slice($guideCategories, 0, 3)),
    ],
    [
        'icon'  => 'folder',
        'color' => 'orange',
        'title' => t('home.projects_heading'),
        'text'  => t('home.projects_text'),
        'links' => [['/projects', t('home.projects_link1')], ['/projects', t('home.projects_link2')], ['/projects', t('home.projects_link3')]],
    ],
    [
        'icon'  => 'info',
        'color' => 'teal',
        'title' => t('home.about_heading'),
        'text'  => t('home.about_text'),
        'links' => [['/about', t('home.about_link1')], ['/about', t('home.about_link2')], ['/contact', t('home.about_link3')]],
    ],
];
?>

<section class="hero">
    <div class="container">
        <p class="eyebrow"><?= icon('sparkles') ?> <?= e($site['name']) ?></p>
        <h1 class="hero-title">
            <?= e(t('home.line1')) ?>
            <span class="gradient-1"><?= e(t('home.line2')) ?></span>
            <span class="gradient-2"><?= e(t('home.line3')) ?></span>
        </h1>
        <p class="hero-lead"><?= e(t('home.intro')) ?></p>
        <div class="hero-actions">
            <a href="/tools" class="button button-primary"><?= e(t('home.cta_tools')) ?> <?= icon('arrow-right') ?></a>
            <a href="/guides" class="button button-secondary"><?= e(t('home.cta_guides')) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <h2><?= icon('wrench') ?> <?= e(t('home.tools_heading')) ?></h2>
            <p><?= e(t('home.tools_text')) ?></p>
        </div>
        <div class="tiles">
            <?php foreach ($tools as $tool): ?>
                <?= tool_tile($tool) ?>
            <?php endforeach; ?>
        </div>
        <div class="center">
            <a href="/tools" class="button button-secondary"><?= e(t('home.tools_all')) ?> <?= icon('arrow-right') ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title"><?= e(t('home.explore')) ?></h2>
        <div class="cards">
            <?php foreach ($explore as $card): ?>
                <article class="card">
                    <span class="card-icon color-<?= e($card['color']) ?>"><?= icon($card['icon']) ?></span>
                    <h3><?= e($card['title']) ?></h3>
                    <p><?= e($card['text']) ?></p>
                    <ul class="link-list">
                        <?php foreach ($card['links'] as [$href, $label]): ?>
                            <li><a href="<?= e($href) ?>"><?= icon('arrow-right') ?> <?= e($label) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
