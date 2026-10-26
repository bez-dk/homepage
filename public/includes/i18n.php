<?php
// Language handling. Texts live in includes/lang/<code>.php.

const LANGUAGES        = ['da' => 'Dansk', 'en' => 'English'];
const DEFAULT_LANGUAGE = 'da';

// Switching language: any page with ?lang=en saves the choice in a cookie
// and reloads the same page without the ?lang part in the address.
if (isset($_GET['lang'], LANGUAGES[$_GET['lang']])) {
    setcookie('lang', $_GET['lang'], [
        'expires'  => time() + 60 * 60 * 24 * 365,
        'path'     => '/',
        'samesite' => 'Lax',
    ]);
    header('Location: ' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), true, 302);
    exit;
}

$lang    = detect_language();
$strings = require __DIR__ . "/lang/$lang.php";

// The same URL shows different languages, so caches must not mix them up.
header('Cache-Control: private, no-cache');
header('Vary: Cookie, Accept-Language');

// Saved choice first, then the browser's preferred language, then the default.
function detect_language(): string
{
    $saved = $_COOKIE['lang'] ?? '';
    if (isset(LANGUAGES[$saved])) {
        return $saved;
    }

    // e.g. "da-DK,da;q=0.9,en;q=0.8" (listed in order of preference)
    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') as $part) {
        $code = strtolower(substr(trim($part), 0, 2));
        if (isset(LANGUAGES[$code])) {
            return $code;
        }
    }

    return DEFAULT_LANGUAGE;
}

// Translate a text key, e.g. t('nav.home') or t('home.heading', ['{name}' => 'Bez']).
function t(string $key, array $vars = []): string
{
    global $strings;
    return strtr($strings[$key] ?? $key, $vars);
}
