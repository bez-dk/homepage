<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/recipes.php';
$pageTitle = t('recipes.title');

// Search words and category from the address, e.g. /recipes?q=kylling&category=dinner
$query    = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 100) : '';
$category = is_string($_GET['category'] ?? null) && isset(RECIPE_CATEGORIES[$_GET['category']]) ? $_GET['category'] : '';

$recipes = search_recipes($query, $category);
$counts  = recipe_category_counts();

// A link to the list with other filters, keeping the rest.
function recipes_link(string $query, string $category): string
{
    $params = array_filter(['q' => $query, 'category' => $category], 'strlen');
    return '/recipes' . ($params ? '?' . http_build_query($params) : '');
}

require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <header class="page-header">
        <h1><?= e(t('recipes.title')) ?></h1>
        <p class="lead"><?= e(t('recipes.lead')) ?></p>
    </header>

    <form class="recipe-search" action="/recipes" method="get" role="search">
        <?= icon('search', 'icon recipe-search-icon') ?>
        <input type="search" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('recipes.search_placeholder')) ?>" aria-label="<?= e(t('recipes.search')) ?>">
        <?php if ($category !== ''): ?>
            <input type="hidden" name="category" value="<?= e($category) ?>">
        <?php endif; ?>
        <button type="submit" class="button button-primary"><?= e(t('recipes.search')) ?></button>
    </form>

    <?php if ($counts): ?>
        <nav class="chips" aria-label="<?= e(t('recipes.categories')) ?>">
            <a href="<?= e(recipes_link($query, '')) ?>" class="chip<?= $category === '' ? ' active' : '' ?>"<?= $category === '' ? ' aria-current="page"' : '' ?>><?= e(t('recipes.all')) ?></a>
            <?php foreach (RECIPE_CATEGORIES as $key => $name): ?>
                <?php if (isset($counts[$key])): ?>
                    <a href="<?= e(recipes_link($query, $key)) ?>" class="chip<?= $category === $key ? ' active' : '' ?>"<?= $category === $key ? ' aria-current="page"' : '' ?>>
                        <?= e(tr($name)) ?> <span class="chip-count"><?= (int) $counts[$key] ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($recipes === null): ?>
        <p class="notice error"><?= e(t('recipes.unavailable')) ?></p>

    <?php elseif (!$recipes): ?>
        <div class="card card-empty recipe-empty">
            <span class="card-icon color-orange"><?= icon('utensils') ?></span>
            <?php if ($query !== '' || $category !== ''): ?>
                <h2><?= e(t('recipes.no_match')) ?></h2>
                <p><a href="/recipes"><?= e(t('recipes.show_all')) ?></a></p>
            <?php else: ?>
                <h2><?= e(t('recipes.empty_title')) ?></h2>
                <p><?= e(t('recipes.empty_text')) ?></p>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <p class="recipe-count" aria-live="polite"><?= e(t(count($recipes) === 1 ? 'recipes.count_one' : 'recipes.count', ['{count}' => count($recipes)])) ?></p>
        <div class="recipe-grid">
            <?php foreach ($recipes as $recipe): ?>
                <?php
                $image = recipe_image_url($recipe);
                $total = (int) $recipe['prep_minutes'] + (int) $recipe['cook_minutes'];
                ?>
                <a class="recipe-card" href="<?= e(recipe_url($recipe)) ?>" lang="<?= e(recipe_lang($recipe)) ?>">
                    <span class="recipe-card-image">
                        <?php if ($image): ?>
                            <img src="<?= e($image) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <?= icon('utensils') ?>
                        <?php endif; ?>
                    </span>
                    <span class="recipe-card-body">
                        <span class="recipe-card-category"><?= e(recipe_category_name($recipe['category'])) ?></span>
                        <span class="recipe-card-title"><?= e(recipe_text($recipe, 'title')) ?></span>
                        <?php if (recipe_text($recipe, 'summary') !== ''): ?>
                            <span class="recipe-card-text"><?= e(recipe_text($recipe, 'summary')) ?></span>
                        <?php endif; ?>
                        <?php if ($total || $recipe['servings']): ?>
                            <span class="recipe-meta">
                                <?php if ($total): ?>
                                    <span><?= icon('clock') ?> <?= e(format_minutes($total)) ?></span>
                                <?php endif; ?>
                                <?php if ($recipe['servings']): ?>
                                    <span><?= icon('users') ?> <?= e(t('recipes.servings_count', ['{count}' => $recipe['servings']])) ?></span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
