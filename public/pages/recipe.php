<?php
// One recipe, served as /recipes/<slug> (see .htaccess and router.php).
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/recipes.php';
require_once __DIR__ . '/../includes/auth.php';

$slug    = is_string($_GET['slug'] ?? null) ? $_GET['slug'] : '';
$isAdmin = is_admin();
$recipe  = preg_match('/^[a-z0-9-]{1,120}$/', $slug) ? find_recipe_by_slug($slug) : null;

// Hidden recipes can only be seen by the admin (as a preview).
if (!$recipe || (!$recipe['published'] && !$isAdmin)) {
    require __DIR__ . '/404.php';
    exit;
}

$recipeLang = recipe_lang($recipe);
$title      = recipe_text($recipe, 'title');
$summary    = recipe_text($recipe, 'summary');
$groups     = ingredient_groups(recipe_text($recipe, 'ingredients'));
$steps      = text_lines(recipe_text($recipe, 'steps'));
$notes      = text_lines(recipe_text($recipe, 'notes'));
$image      = recipe_image_url($recipe);

$times = array_filter([
    'prep'  => (int) $recipe['prep_minutes'],
    'cook'  => (int) $recipe['cook_minutes'],
    'total' => $recipe['prep_minutes'] && $recipe['cook_minutes'] ? $recipe['prep_minutes'] + $recipe['cook_minutes'] : 0,
]);

$pageTitle = $title;
require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="/recipes"><?= e(t('recipes.title')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="/recipes?category=<?= e($recipe['category']) ?>"><?= e(recipe_category_name($recipe['category'])) ?></a>
    </nav>

    <article class="recipe" lang="<?= e($recipeLang) ?>">
        <header class="page-header page-header-tool recipe-header">
            <?php if (!$recipe['published']): ?>
                <p class="notice error" lang="<?= e($lang) ?>"><?= e(t('recipes.hidden_notice')) ?></p>
            <?php endif; ?>
            <?php if ($recipeLang !== $lang): ?>
                <p class="form-hint" lang="<?= e($lang) ?>"><?= e(t('recipes.only_other_language')) ?></p>
            <?php endif; ?>

            <h1><?= e($title) ?></h1>
            <?php if ($summary !== ''): ?>
                <p class="lead"><?= e($summary) ?></p>
            <?php endif; ?>

            <div class="recipe-facts" lang="<?= e($lang) ?>">
                <?php foreach ($times as $key => $minutes): ?>
                    <span><?= icon('clock') ?> <?= e(t("recipes.time.$key")) ?>: <strong><?= e(format_minutes($minutes)) ?></strong></span>
                <?php endforeach; ?>
                <?php if ($recipe['servings']): ?>
                    <span><?= icon('users') ?> <?= e(t('recipes.servings')) ?>: <strong><?= (int) $recipe['servings'] ?></strong></span>
                <?php endif; ?>
            </div>

            <div class="recipe-actions" lang="<?= e($lang) ?>">
                <button type="button" class="button button-secondary" data-print><?= icon('printer') ?> <?= e(t('recipes.print')) ?></button>
                <?php if ($isAdmin): ?>
                    <a class="button button-secondary" href="/admin/recipe?id=<?= (int) $recipe['id'] ?>"><?= icon('pencil') ?> <?= e(t('recipes.edit')) ?></a>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($image): ?>
            <img class="recipe-image" src="<?= e($image) ?>" alt="<?= e($title) ?>">
        <?php endif; ?>

        <div class="recipe-layout">
            <section class="card recipe-ingredients" aria-labelledby="ingredients-title">
                <h2 id="ingredients-title" lang="<?= e($lang) ?>"><?= e(t('recipes.ingredients')) ?></h2>

                <?php if ($recipe['servings']): ?>
                    <div class="servings" data-servings="<?= (int) $recipe['servings'] ?>" lang="<?= e($lang) ?>" hidden>
                        <span><?= e(t('recipes.servings')) ?></span>
                        <button type="button" class="servings-button" data-step="-1" aria-label="<?= e(t('recipes.fewer')) ?>"><?= icon('minus') ?></button>
                        <output aria-live="polite"><?= (int) $recipe['servings'] ?></output>
                        <button type="button" class="servings-button" data-step="1" aria-label="<?= e(t('recipes.more')) ?>"><?= icon('plus') ?></button>
                    </div>
                <?php endif; ?>

                <?php foreach ($groups as $group): ?>
                    <?php if ($group['heading'] !== null): ?>
                        <h3><?= e($group['heading']) ?></h3>
                    <?php endif; ?>
                    <ul class="ingredient-list">
                        <?php foreach ($group['items'] as $item): ?>
                            <li><?= ingredient_html($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </section>

            <section class="recipe-steps" aria-labelledby="steps-title">
                <h2 id="steps-title" lang="<?= e($lang) ?>"><?= e(t('recipes.steps')) ?></h2>
                <ol class="step-list">
                    <?php foreach ($steps as $step): ?>
                        <li><?= e($step) ?></li>
                    <?php endforeach; ?>
                </ol>

                <?php if ($notes): ?>
                    <aside class="recipe-notes">
                        <h2 lang="<?= e($lang) ?>"><?= e(t('recipes.notes')) ?></h2>
                        <?php foreach ($notes as $note): ?>
                            <p><?= e($note) ?></p>
                        <?php endforeach; ?>
                    </aside>
                <?php endif; ?>
            </section>
        </div>
    </article>
</div>

<script src="/assets/js/recipe.js" defer></script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
