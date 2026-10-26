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

$file = $path === '/' ? '/index.php' : $path . '.php';

if (!str_starts_with($path, '/includes') && is_file($root . $file)) {
    require $root . $file;
} else {
    require $root . '/404.php';
}
