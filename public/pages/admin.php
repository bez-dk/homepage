<?php
// Admin: login, and the list of recipes once logged in.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/recipes.php';
require_once __DIR__ . '/../includes/auth.php';

start_session(); // before any output, as the forms below need it
$pageTitle   = t('admin.title');
$noindex     = true;
$db          = db();
$hash        = db_config()['admin_password_hash'] ?? '';
$error       = null;
$madeHash    = null;
$installFail = false;

// Make the tables the first time (does nothing once they exist).
if ($db) {
    try {
        recipes_install($db);
    } catch (PDOException $exception) {
        error_log('Recipe install failed: ' . $exception->getMessage());
        $installFail = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!check_csrf()) {
        $error = 'admin.error.session';
    } elseif ($action === 'logout') {
        admin_logout();
        header('Location: /admin', true, 303);
        exit;
    } elseif ($action === 'login' && $db && $hash !== '') {
        $error = admin_login($password);
        if ($error === null) {
            header('Location: /admin', true, 303);
            exit;
        }
    } elseif ($action === 'hash' && $hash === '') {
        // Only makes a hash for you to copy; it doesn't change anything on the server.
        if (mb_strlen($password) < 12) {
            $error = 'admin.error.short_password';
        } else {
            $madeHash = password_hash($password, PASSWORD_DEFAULT);
        }
    }
}

$isAdmin = is_admin();
$query   = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 100) : '';
$recipes = $isAdmin ? search_recipes($query, '', true) : null;

require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <header class="page-header admin-header">
        <h1><?= e(t('admin.title')) ?></h1>
        <?php if ($isAdmin): ?>
            <div class="admin-header-actions">
                <a class="button button-primary" href="/admin/recipe"><?= icon('plus') ?> <?= e(t('admin.new')) ?></a>
                <form method="post" action="/admin">
                    <?= csrf_field() ?>
                    <button type="submit" name="action" value="logout" class="button button-secondary"><?= e(t('admin.logout')) ?></button>
                </form>
            </div>
        <?php endif; ?>
    </header>

    <?php if ($error): ?>
        <p class="notice error" role="alert"><?= e(t($error, ['{minutes}' => LOGIN_LOCK_MINUTES])) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <p class="notice success"><?= e(t('admin.deleted')) ?></p>
    <?php endif; ?>

    <?php if (!db_config()): ?>
        <div class="card form-card prose">
            <h2><?= e(t('admin.setup_title')) ?></h2>
            <p><?= e(t('admin.setup_config')) ?></p>
        </div>

    <?php elseif (!$db || $installFail): ?>
        <p class="notice error"><?= e(t('admin.setup_db')) ?></p>

    <?php elseif ($hash === ''): ?>
        <div class="card form-card">
            <h2><?= e(t('admin.hash_title')) ?></h2>
            <p><?= e(t('admin.hash_text')) ?></p>
            <?php if ($madeHash): ?>
                <p class="notice success"><?= e(t('admin.hash_done')) ?></p>
                <pre class="admin-hash"><code>'admin_password_hash' => '<?= e($madeHash) ?>',</code></pre>
            <?php else: ?>
                <form method="post" action="/admin" class="contact-form">
                    <?= csrf_field() ?>
                    <label>
                        <?= e(t('admin.new_password')) ?>
                        <input type="password" name="password" autocomplete="new-password" minlength="12" required>
                    </label>
                    <button type="submit" name="action" value="hash" class="button button-primary"><?= e(t('admin.make_hash')) ?></button>
                </form>
            <?php endif; ?>
        </div>

    <?php elseif (!$isAdmin): ?>
        <div class="card form-card">
            <h2><?= e(t('admin.login_title')) ?></h2>
            <form method="post" action="/admin" class="contact-form">
                <?= csrf_field() ?>
                <label>
                    <?= e(t('admin.password')) ?>
                    <input type="password" name="password" autocomplete="current-password" required autofocus>
                </label>
                <button type="submit" name="action" value="login" class="button button-primary"><?= e(t('admin.login')) ?></button>
            </form>
        </div>

    <?php else: ?>
        <form class="recipe-search" action="/admin" method="get" role="search">
            <?= icon('search', 'icon recipe-search-icon') ?>
            <input type="search" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('recipes.search_placeholder')) ?>" aria-label="<?= e(t('recipes.search')) ?>">
            <button type="submit" class="button button-primary"><?= e(t('recipes.search')) ?></button>
        </form>

        <?php if (!$recipes): ?>
            <div class="card card-empty recipe-empty">
                <span class="card-icon color-orange"><?= icon('utensils') ?></span>
                <h2><?= e(t($query !== '' ? 'recipes.no_match' : 'admin.empty')) ?></h2>
            </div>
        <?php else: ?>
            <div class="card admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col"><?= e(t('admin.col.title')) ?></th>
                            <th scope="col"><?= e(t('admin.col.category')) ?></th>
                            <th scope="col"><?= e(t('admin.col.languages')) ?></th>
                            <th scope="col"><?= e(t('admin.col.status')) ?></th>
                            <th scope="col"><?= e(t('admin.col.updated')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipes as $recipe): ?>
                            <tr>
                                <td><a href="/admin/recipe?id=<?= (int) $recipe['id'] ?>"><?= e(recipe_text($recipe, 'title')) ?></a></td>
                                <td><?= e(recipe_category_name($recipe['category'])) ?></td>
                                <td><?= e(implode(' + ', array_keys(array_filter(['DA' => $recipe['title_da'], 'EN' => $recipe['title_en']], 'strlen')))) ?></td>
                                <td>
                                    <span class="badge <?= $recipe['published'] ? 'badge-success' : 'badge-soft' ?>">
                                        <?= e(t($recipe['published'] ? 'admin.published' : 'admin.hidden')) ?>
                                    </span>
                                </td>
                                <td><?= e(date('d-m-Y', strtotime($recipe['updated_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
