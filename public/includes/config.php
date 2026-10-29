<?php
// Site-wide settings. Edit these to make the site your own.
// Page texts are in includes/lang/da.php and en.php,
// tools and guides are listed in includes/content.php.

$site = [
    'name' => 'BEZ.dk',

    // Where contact form messages are sent.
    'contact_to'   => 'you@example.com',
    // one.com requires the sender to be an address on your own domain,
    // otherwise mail may be rejected or marked as spam.
    'contact_from' => 'noreply@yourdomain.com',
];

// Main navigation: URL => text key (see the language files)
$nav = [
    '/'         => 'nav.home',
    '/tools'    => 'nav.tools',
    '/guides'   => 'nav.guides',
    '/projects' => 'nav.projects',
    '/about'    => 'nav.about',
];

// Small helper to safely print text in HTML.
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/content.php';
