<?php
// Admin: add a new recipe (/admin/recipe) or edit one (/admin/recipe?id=5).
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/recipes.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session(); // before any output, as the form below needs it
$noindex = true;
$isAdmin = is_admin();
$isPost  = $_SERVER['REQUEST_METHOD'] === 'POST';

// Not logged in: go to the login. (A post is kept, so a long recipe isn't lost if the login ran out.)
if ((!$isAdmin && !$isPost) || !db()) {
    header('Location: /admin', true, 302);
    exit;
}

$id      = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$current = $id ? find_recipe_by_id($id) : empty_recipe();
if (!$current) {
    require __DIR__ . '/../404.php';
    exit;
}

$recipe = $current;
$errors = [];

if ($isPost) {
    if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // PHP drops the whole form when the upload is bigger than the server allows.
        $errors[] = 'admin.error.image_size';
    } elseif (!$isAdmin || !check_csrf()) {
        [$recipe] = recipe_from_form($_POST, $current);
        $errors[] = 'admin.error.session';
    } elseif (($_POST['action'] ?? '') === 'delete' && $current['id']) {
        delete_recipe($current);
        header('Location: /admin?deleted=1', true, 303);
        exit;
    } else {
        [$recipe, $errors] = recipe_from_form($_POST, $current);
        if (!$errors) {
            $newImage = null;
            try {
                $newImage = store_recipe_image($_FILES['image'] ?? []);
                if ($newImage || !empty($_POST['remove_image'])) {
                    $recipe['image'] = $newImage;
                }
                $savedId = save_recipe($recipe);
                if ($recipe['image'] !== $current['image']) {
                    delete_recipe_image($current['image']);
                }
                header('Location: /admin/recipe?id=' . $savedId . '&saved=1', true, 303);
                exit;
            } catch (PDOException $exception) {
                error_log('Saving recipe failed: ' . $exception->getMessage());
                delete_recipe_image($newImage);
                $recipe['image'] = $current['image'];
                $errors[] = 'admin.error.save';
            } catch (RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    }
}

$pageTitle = $current['id'] ? t('admin.edit') : t('admin.new');
$image     = $recipe['image'] ? recipe_image_url($recipe) : null;
$action    = '/admin/recipe' . ($current['id'] ? '?id=' . (int) $current['id'] : '');
$languages = ['da' => 'Dansk', 'en' => 'English'];

require __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="/admin"><?= e(t('admin.title')) ?></a>
        <span aria-hidden="true">/</span>
        <span aria-current="page"><?= e($pageTitle) ?></span>
    </nav>

    <header class="page-header page-header-tool admin-header">
        <h1><?= e($current['id'] ? recipe_text($current, 'title') : t('admin.new')) ?></h1>
        <?php if ($current['id']): ?>
            <a class="button button-secondary" href="<?= e(recipe_url($current)) ?>"><?= e(t('admin.view')) ?> <?= icon('arrow-right') ?></a>
        <?php endif; ?>
    </header>

    <?php if (isset($_GET['saved']) && !$errors): ?>
        <p class="notice success" role="status"><?= e(t('admin.saved')) ?></p>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="notice error" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= e(t($error, ['{size}' => RECIPE_IMAGE_MAX / 1024 / 1024])) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="admin-form" data-unsaved="<?= e(t('admin.unsaved')) ?>">
        <?= csrf_field() ?>

        <section class="card admin-section">
            <h2><?= e(t('admin.general')) ?></h2>
            <div class="admin-fields">
                <label>
                    <?= e(t('admin.field.category')) ?>
                    <select name="category">
                        <?php foreach (RECIPE_CATEGORIES as $key => $name): ?>
                            <option value="<?= e($key) ?>"<?= $key === $recipe['category'] ? ' selected' : '' ?>><?= e(tr($name)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.field.servings')) ?>
                    <input type="number" name="servings" value="<?= e((string) $recipe['servings']) ?>" min="1" max="9999" inputmode="numeric">
                </label>
                <label>
                    <?= e(t('admin.field.prep')) ?>
                    <input type="number" name="prep_minutes" value="<?= e((string) $recipe['prep_minutes']) ?>" min="1" max="9999" inputmode="numeric">
                </label>
                <label>
                    <?= e(t('admin.field.cook')) ?>
                    <input type="number" name="cook_minutes" value="<?= e((string) $recipe['cook_minutes']) ?>" min="1" max="9999" inputmode="numeric">
                </label>
                <label class="admin-field-wide">
                    <?= e(t('admin.field.slug')) ?>
                    <input type="text" name="slug" value="<?= e($recipe['slug']) ?>" maxlength="100" spellcheck="false" aria-describedby="slug-hint">
                    <span id="slug-hint" class="form-hint"><?= e(t('admin.field.slug_hint')) ?></span>
                </label>
                <div class="admin-field-wide admin-image">
                    <?php if ($image): ?>
                        <img src="<?= e($image) ?>" alt="">
                    <?php endif; ?>
                    <div>
                        <label>
                            <?= e(t($image ? 'admin.field.image_replace' : 'admin.field.image')) ?>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="image-hint">
                            <span id="image-hint" class="form-hint"><?= e(t('admin.field.image_hint', ['{size}' => RECIPE_IMAGE_MAX / 1024 / 1024])) ?></span>
                        </label>
                        <?php if ($image): ?>
                            <label class="checkbox">
                                <input type="checkbox" name="remove_image" value="1"> <?= e(t('admin.field.image_remove')) ?>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
                <label class="checkbox admin-field-wide">
                    <input type="checkbox" name="published" value="1"<?= $recipe['published'] ? ' checked' : '' ?>>
                    <?= e(t('admin.field.published')) ?>
                </label>
            </div>
        </section>

        <div class="admin-languages">
            <?php foreach ($languages as $code => $languageName): ?>
                <section class="card admin-section" lang="<?= e($code) ?>">
                    <h2><img class="flag" src="/assets/img/flags/<?= e($code) ?>.svg" alt="" width="24" height="16"> <?= e($languageName) ?></h2>
                    <div class="admin-fields admin-fields-stacked" lang="<?= e($lang) ?>">
                        <label>
                            <?= e(t('admin.field.title')) ?>
                            <input type="text" name="title_<?= $code ?>" value="<?= e($recipe["title_$code"]) ?>" maxlength="200" lang="<?= e($code) ?>">
                        </label>
                        <label>
                            <?= e(t('admin.field.summary')) ?>
                            <textarea name="summary_<?= $code ?>" rows="3" lang="<?= e($code) ?>"><?= e($recipe["summary_$code"]) ?></textarea>
                        </label>
                        <label>
                            <?= e(t('admin.field.ingredients')) ?>
                            <textarea name="ingredients_<?= $code ?>" rows="12" lang="<?= e($code) ?>"><?= e($recipe["ingredients_$code"]) ?></textarea>
                            <span class="form-hint"><?= e(t('admin.field.ingredients_hint')) ?></span>
                        </label>
                        <label>
                            <?= e(t('admin.field.steps')) ?>
                            <textarea name="steps_<?= $code ?>" rows="12" lang="<?= e($code) ?>"><?= e($recipe["steps_$code"]) ?></textarea>
                            <span class="form-hint"><?= e(t('admin.field.steps_hint')) ?></span>
                        </label>
                        <label>
                            <?= e(t('admin.field.notes')) ?>
                            <textarea name="notes_<?= $code ?>" rows="4" lang="<?= e($code) ?>"><?= e($recipe["notes_$code"]) ?></textarea>
                        </label>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
        <p class="form-hint"><?= e(t('admin.languages_hint')) ?></p>

        <div class="admin-form-actions">
            <button type="submit" class="button button-primary"><?= icon('check') ?> <?= e(t('admin.save')) ?></button>
            <a class="button button-secondary" href="/admin"><?= e(t('admin.cancel')) ?></a>
        </div>
    </form>

    <?php if ($current['id']): ?>
        <form method="post" action="<?= e($action) ?>" class="admin-delete" data-confirm="<?= e(t('admin.delete_confirm')) ?>">
            <?= csrf_field() ?>
            <button type="submit" name="action" value="delete" class="button button-danger"><?= icon('trash') ?> <?= e(t('admin.delete')) ?></button>
        </form>
    <?php endif; ?>
</div>

<script src="/assets/js/admin.js" defer></script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
