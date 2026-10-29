<?php
require_once __DIR__ . '/config.php';

// Current URL path without trailing slash, e.g. "/tools" (or "/" for the homepage).
$currentPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$pageTitle   = isset($pageTitle) ? $pageTitle . ' – ' . $site['name'] : $site['name'] . ' – ' . t('site.tagline');

// A menu item is active on its own page and on pages below it (e.g. /tools/...).
function is_active(string $url, string $currentPath): bool
{
    return $url === '/' ? $currentPath === '/' : str_starts_with($currentPath . '/', $url . '/');
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(t('site.description')) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="/assets/img/logo.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="theme-color" content="#4f46e5">
    <script>
        // Set the theme before the page is drawn, so it doesn't flash.
        (function () {
            var theme;
            try { theme = localStorage.getItem('theme'); } catch (e) {}
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.classList.add('js');
        })();
    </script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/main.js" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="/" class="logo">
                <img class="logo-mark" src="/assets/img/logo.svg" alt="" width="32" height="32">
                <span class="logo-text"><?= e($site['name']) ?></span>
            </a>

            <nav id="site-nav" class="site-nav">
                <?php foreach ($nav as $url => $label): ?>
                    <a href="<?= e($url) ?>"<?= is_active($url, $currentPath) ? ' class="active" aria-current="page"' : '' ?>><?= e(t($label)) ?></a>
                <?php endforeach; ?>
            </nav>

            <div class="header-actions">
                <details class="dropdown lang-menu">
                    <summary class="header-button" aria-label="<?= e(t('nav.language') . ': ' . LANGUAGES[$lang]) ?>">
                        <img class="flag" src="/assets/img/flags/<?= e($lang) ?>.svg" alt="" width="24" height="16">
                        <span class="lang-name"><?= e(LANGUAGES[$lang]) ?></span>
                    </summary>
                    <div class="dropdown-menu">
                        <?php foreach (LANGUAGES as $code => $languageName): ?>
                            <a href="?lang=<?= e($code) ?>" lang="<?= e($code) ?>" hreflang="<?= e($code) ?>" class="dropdown-item<?= $code === $lang ? ' active' : '' ?>"<?= $code === $lang ? ' aria-current="true"' : '' ?>><img class="flag" src="/assets/img/flags/<?= e($code) ?>.svg" alt="" width="24" height="16"> <?= e($languageName) ?></a>
                        <?php endforeach; ?>
                    </div>
                </details>

                <button class="header-button theme-toggle" type="button" aria-label="<?= e(t('nav.theme')) ?>" title="<?= e(t('nav.theme')) ?>">
                    <?= icon('moon', 'icon icon-moon') ?>
                    <?= icon('sun', 'icon icon-sun') ?>
                </button>

                <button class="header-button nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="<?= e(t('nav.menu')) ?>">
                    <span class="nav-toggle-bar"></span>
                </button>
            </div>
        </div>
    </header>
    <main>
