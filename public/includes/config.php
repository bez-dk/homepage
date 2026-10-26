<?php
// Site-wide settings. Edit these to make the site your own.
// Texts shown on the pages are in includes/lang/da.php and en.php.

$site = [
    'name' => 'Bez',

    // Where contact form messages are sent.
    'contact_to'   => 'you@example.com',
    // one.com requires the sender to be an address on your own domain,
    // otherwise mail may be rejected or marked as spam.
    'contact_from' => 'noreply@yourdomain.com',
];

// Navigation: URL => text key (see the language files)
$nav = [
    '/'        => 'nav.home',
    '/about'   => 'nav.about',
    '/contact' => 'nav.contact',
];

// Small helper to safely print text in HTML.
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/i18n.php';
