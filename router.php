<?php
// Router for PHP's built-in server, so clean URLs work locally like they do
// with .htaccess on one.com. Not uploaded. Run from the project folder:
//   php -S localhost:8000 -t public router.php

$root = __DIR__ . '/public';
$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

// Static files (CSS, images, ...) are served as-is.
if ($path !== '/' && is_file($root . $path) && !str_ends_with($path, '.php')) {
    return false;
}

// Pages live in public/pages: /about -> pages/about.php
$pages = $root . '/pages';
$file  = $path === '/' ? '/index.php' : $path . '.php';

// A single recipe: /recipes/aeblekage -> pages/recipe.php?slug=aeblekage
if (preg_match('#^/recipes/([a-z0-9-]+)$#', $path, $match)) {
    $_GET['slug'] = $match[1];
    require $pages . '/recipe.php';
} elseif (!str_contains($path, '..') && is_file($pages . $file)) {
    require $pages . $file;
} else {
    require $pages . '/404.php';
}
