<?php
// Tools and guides shown on the site, in Danish and English.
// When a tool or guide is ready, give it a 'url' and it becomes a link.

// Pick the text for the current language from ['da' => ..., 'en' => ...].
function tr(array $text): string
{
    global $lang;
    return $text[$lang] ?? $text[DEFAULT_LANGUAGE];
}

$toolCategories = [
    'engineering' => ['da' => 'Teknik & DIY', 'en' => 'Engineering & DIY'],
    'it'          => ['da' => 'Netværk & udvikling', 'en' => 'Networking & development'],
    'finance'     => ['da' => 'Økonomi', 'en' => 'Finance'],
];

$tools = [
    [
        'slug'     => 'ip-subnet-calculator',
        'category' => 'it',
        'icon'     => 'network',
        'color'    => 'teal',
        'title'    => ['da' => 'IP-subnetberegner', 'en' => 'IP Subnet Calculator'],
        'text'     => ['da' => 'Beregn subnet, IP-adresser og netværksområder.', 'en' => 'Calculate subnets, IP addresses and network ranges.'],
    ],
    [
        'slug'     => 'voltage-drop-calculator',
        'category' => 'engineering',
        'icon'     => 'zap',
        'color'    => 'orange',
        'title'    => ['da' => 'Spændingsfald i kabler', 'en' => 'Cable Voltage Drop Calculator'],
        'text'     => ['da' => 'Beregn spændingsfaldet i et kabel.', 'en' => 'Calculate the voltage drop in a cable.'],
    ],
    [
        'slug'     => 'compound-interest-calculator',
        'category' => 'finance',
        'icon'     => 'trending-up',
        'color'    => 'violet',
        'title'    => ['da' => 'Renters rente-beregner', 'en' => 'Compound Interest Calculator'],
        'text'     => ['da' => 'Beregn renters rente og investeringer.', 'en' => 'Calculate compound interest and investments.'],
    ],
    [
        'slug'     => 'concrete-calculator',
        'category' => 'engineering',
        'icon'     => 'box',
        'color'    => 'blue',
        'title'    => ['da' => 'Betonberegner', 'en' => 'Concrete Calculator'],
        'text'     => ['da' => 'Beregn betonmængder til støbning.', 'en' => 'Calculate concrete volumes for casting.'],
    ],
    [
        'slug'     => 'soil-volume-calculator',
        'category' => 'engineering',
        'icon'     => 'mountain',
        'color'    => 'amber',
        'title'    => ['da' => 'Jordberegner', 'en' => 'Soil Volume Calculator'],
        'text'     => ['da' => 'Beregn jordmængder, fx til støjvolde.', 'en' => 'Calculate soil volumes, e.g. for noise barriers.'],
    ],
    [
        'slug'     => 'json-formatter',
        'category' => 'it',
        'icon'     => 'braces',
        'color'    => 'pink',
        'title'    => ['da' => 'JSON-formatering', 'en' => 'JSON Formatter'],
        'text'     => ['da' => 'Formatér og validér JSON.', 'en' => 'Format and validate JSON.'],
    ],
    [
        'slug'     => 'loan-calculator',
        'category' => 'finance',
        'icon'     => 'landmark',
        'color'    => 'indigo',
        'title'    => ['da' => 'Låneberegner', 'en' => 'Loan Calculator'],
        'text'     => ['da' => 'Beregn og sammenlign lån.', 'en' => 'Calculate and compare loans.'],
    ],
    [
        'slug'     => 'unit-converter',
        'category' => 'engineering',
        'icon'     => 'arrows',
        'color'    => 'purple',
        'title'    => ['da' => 'Enhedsomregner', 'en' => 'Unit Converter'],
        'text'     => ['da' => 'Omregn mellem enheder.', 'en' => 'Convert between units.'],
    ],
];

$guideCategories = [
    [
        'id'    => 'networking',
        'icon'  => 'network',
        'color' => 'blue',
        'title' => ['da' => 'Netværk', 'en' => 'Networking'],
        'text'  => ['da' => 'Kabler, fiber og netværk mellem bygninger.', 'en' => 'Cables, fibre and networks between buildings.'],
        'guides' => [
            ['da' => 'Fiber vs. Ethernet: Hvad skal du vælge?', 'en' => 'Fibre vs. Ethernet: Which should you choose?'],
            ['da' => 'Hvor langt kan du trække et Ethernet-kabel?', 'en' => 'How far can you run an Ethernet cable?'],
            ['da' => 'Sådan beregner du spændingsfald', 'en' => 'How to calculate voltage drop'],
            ['da' => 'Sådan planlægger du et netværk mellem to bygninger', 'en' => 'Planning a network between two buildings'],
        ],
    ],
    [
        'id'    => 'smart-home',
        'icon'  => 'home',
        'color' => 'teal',
        'title' => ['da' => 'Smart Home', 'en' => 'Smart Home'],
        'text'  => ['da' => 'Home Assistant, Zigbee og lokale netværkstjenester.', 'en' => 'Home Assistant, Zigbee and local network services.'],
        'guides' => [
            ['da' => 'Home Assistant: Kom godt i gang', 'en' => 'Home Assistant: Getting started'],
            ['da' => 'Home Assistant på Proxmox', 'en' => 'Home Assistant on Proxmox'],
            ['da' => 'Zigbee vs. Wi-Fi', 'en' => 'Zigbee vs. Wi-Fi'],
            ['da' => 'Lokal DNS til dit hjemmenetværk', 'en' => 'Local DNS for your home network'],
        ],
    ],
    [
        'id'    => 'development',
        'icon'  => 'code',
        'color' => 'violet',
        'title' => ['da' => 'Udvikling', 'en' => 'Development'],
        'text'  => ['da' => 'Python, JSON, SQLite og automatisering.', 'en' => 'Python, JSON, SQLite and automation.'],
        'guides' => [
            ['da' => 'Python-guides', 'en' => 'Python guides'],
            ['da' => 'Arbejde med JSON', 'en' => 'Working with JSON'],
            ['da' => 'SQLite i praksis', 'en' => 'SQLite in practice'],
            ['da' => 'Automatisering af gentagne opgaver', 'en' => 'Automating repetitive tasks'],
        ],
    ],
    [
        'id'    => 'diy',
        'icon'  => 'ruler',
        'color' => 'orange',
        'title' => ['da' => 'DIY & teknik', 'en' => 'DIY & Engineering'],
        'text'  => ['da' => 'Beregninger og planlægning til byggeprojekter.', 'en' => 'Calculations and planning for building projects.'],
        'guides' => [
            ['da' => 'Beregning af betonmængder', 'en' => 'Calculating concrete volumes'],
            ['da' => 'Beregning af jordvolumen', 'en' => 'Calculating soil volume'],
            ['da' => 'Planlægning af et værksted', 'en' => 'Planning a workshop'],
            ['da' => 'Materialeforbrug ved forskellige konstruktioner', 'en' => 'Material usage for different constructions'],
        ],
    ],
];

// A colored tile for a tool (used on the home page and the tools page).
function tool_tile(array $tool, bool $showText = false): string
{
    global $toolCategories;
    $tag   = isset($tool['url']) ? 'a' : 'div';
    $href  = isset($tool['url']) ? ' href="' . e($tool['url']) . '"' : '';
    $badge = isset($tool['url']) ? '' : '<span class="badge">' . e(t('common.coming_soon')) . '</span>';
    $text  = $showText ? '<span class="tile-text">' . e(tr($tool['text'])) . '</span>' : '';

    return '<' . $tag . $href . ' class="tile tile-' . e($tool['color']) . '">'
        . icon($tool['icon'], 'icon tile-icon')
        . '<span class="tile-title">' . e(tr($tool['title'])) . '</span>'
        . '<span class="tile-category">' . e(tr($toolCategories[$tool['category']])) . '</span>'
        . $text . $badge
        . '</' . $tag . '>';
}
