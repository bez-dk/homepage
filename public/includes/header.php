<?php
require_once __DIR__ . '/config.php';

// Current URL path without trailing slash, e.g. "/about" (or "/" for the homepage).
$currentPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$pageTitle   = isset($pageTitle) ? $pageTitle . ' – ' . $site['name'] : $site['name'];
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(t('site.description')) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>document.documentElement.classList.add('js');</script>
    <script src="/assets/js/main.js" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="/" class="logo"><?= e($site['name']) ?></a>

            <nav id="site-nav" class="site-nav">
                <?php foreach ($nav as $url => $label): ?>
                    <a href="<?= e($url) ?>"<?= $url === $currentPath ? ' class="active" aria-current="page"' : '' ?>><?= e(t($label)) ?></a>
                <?php endforeach; ?>
            </nav>

            <details class="dropdown lang-menu">
                <summary aria-label="<?= e(t('nav.language') . ': ' . LANGUAGES[$lang]) ?>">
                    <img class="flag" src="/assets/img/flags/<?= e($lang) ?>.svg" alt="" width="24" height="16">
                    <span class="lang-name"><?= e(LANGUAGES[$lang]) ?></span>
                </summary>
                <div class="dropdown-menu">
                    <?php foreach (LANGUAGES as $code => $languageName): ?>
                        <a href="?lang=<?= e($code) ?>" lang="<?= e($code) ?>" hreflang="<?= e($code) ?>" class="dropdown-item<?= $code === $lang ? ' active' : '' ?>"<?= $code === $lang ? ' aria-current="true"' : '' ?>><img class="flag" src="/assets/img/flags/<?= e($code) ?>.svg" alt="" width="24" height="16"> <?= e($languageName) ?></a>
                    <?php endforeach; ?>
                </div>
            </details>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="<?= e(t('nav.menu')) ?>">
                <span class="nav-toggle-bar"></span>
            </button>
        </div>
    </header>
    <main class="container">
        <div class="panel">
