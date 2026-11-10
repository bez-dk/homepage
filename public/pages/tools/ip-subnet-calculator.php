<?php
require_once __DIR__ . '/../../includes/config.php';
$tool      = find_tool('ip-subnet-calculator');
$pageTitle = tr($tool['title']);
require __DIR__ . '/../../includes/header.php';

// Starting values: from the address (?ip=...&prefix=..., so a result can be shared) or an example.
$ip     = is_string($_GET['ip'] ?? null) ? trim($_GET['ip']) : '192.168.1.10';
$prefix = filter_var($_GET['prefix'] ?? 24, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 32]]);
$prefix = $prefix === false ? 24 : $prefix;

// Result rows, filled in by the script. Key => text key in the language files.
$resultFields = ['address', 'cidr', 'network', 'broadcast', 'mask', 'wildcard', 'first', 'last', 'hosts', 'total', 'class', 'type'];
$binaryFields = ['address', 'mask', 'network'];

// Texts the script needs, in the current language.
$addressTypes = ['this_network', 'private', 'shared', 'loopback', 'link_local', 'documentation', 'multicast', 'broadcast', 'reserved', 'public'];
$scriptTexts  = [
    'none'   => t('subnet.none'),
    'note31' => t('subnet.note31'),
    'note32' => t('subnet.note32'),
    'errors' => ['ip' => t('subnet.error.ip'), 'prefix' => t('subnet.error.prefix')],
    'types'  => array_combine($addressTypes, array_map(fn ($type) => t("subnet.type.$type"), $addressTypes)),
];

// "/24 – 255.255.255.0"
function prefix_label(int $prefix): string
{
    $mask = $prefix === 0 ? 0 : (0xFFFFFFFF << (32 - $prefix)) & 0xFFFFFFFF;
    return '/' . $prefix . ' – ' . long2ip($mask);
}
?>

<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="/tools"><?= e(t('nav.tools')) ?></a>
        <span aria-hidden="true">/</span>
        <span aria-current="page"><?= e(tr($tool['title'])) ?></span>
    </nav>

    <header class="page-header page-header-tool">
        <h1><?= e(tr($tool['title'])) ?></h1>
        <p class="lead"><?= e(tr($tool['text'])) ?></p>
    </header>

    <noscript><p class="notice error"><?= e(t('subnet.noscript')) ?></p></noscript>

    <div class="tool-layout">
        <section class="card" aria-labelledby="subnet-form-title">
            <h2 id="subnet-form-title"><?= e(t('subnet.form_title')) ?></h2>
            <form id="subnet-form" class="tool-form" action="/tools/ip-subnet-calculator" method="get" novalidate>
                <label>
                    <?= e(t('subnet.ip')) ?>
                    <input type="text" name="ip" value="<?= e($ip) ?>" inputmode="decimal" autocomplete="off" spellcheck="false" aria-describedby="subnet-ip-hint" required>
                    <span id="subnet-ip-hint" class="form-hint"><?= e(t('subnet.ip_hint')) ?></span>
                </label>
                <label>
                    <?= e(t('subnet.prefix')) ?>
                    <select name="prefix">
                        <?php for ($p = 32; $p >= 0; $p--): ?>
                            <option value="<?= $p ?>"<?= $p === $prefix ? ' selected' : '' ?>><?= e(prefix_label($p)) ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <button type="submit" class="button button-primary"><?= icon('network') ?> <?= e(t('subnet.calculate')) ?></button>
            </form>
        </section>

        <section class="card" aria-labelledby="subnet-results-title">
            <h2 id="subnet-results-title"><?= e(t('subnet.results')) ?></h2>
            <p id="subnet-error" class="notice error" role="alert" hidden></p>

            <div id="subnet-results" aria-live="polite" hidden>
                <dl class="result-list">
                    <?php foreach ($resultFields as $field): ?>
                        <div>
                            <dt><?= e(t("subnet.result.$field")) ?></dt>
                            <dd data-field="<?= e($field) ?>"></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
                <p class="form-hint" data-field="note" hidden></p>

                <h3><?= e(t('subnet.binary')) ?></h3>
                <p class="form-hint"><?= e(t('subnet.binary_text')) ?></p>
                <dl class="result-list result-binary">
                    <?php foreach ($binaryFields as $field): ?>
                        <div>
                            <dt><?= e(t("subnet.result.$field")) ?></dt>
                            <dd data-binary="<?= e($field) ?>"></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </section>
    </div>

    <section class="prose tool-info">
        <h2><?= e(t('subnet.howto_title')) ?></h2>
        <p><?= e(t('subnet.howto1')) ?></p>
        <p><?= e(t('subnet.howto2')) ?></p>
        <p><?= e(t('subnet.howto3')) ?></p>
    </section>
</div>

<script type="application/json" id="subnet-texts"><?= json_encode($scriptTexts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="/assets/js/tools/ip-subnet-calculator.js" defer></script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
