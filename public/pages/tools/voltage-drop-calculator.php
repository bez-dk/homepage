<?php
require_once __DIR__ . '/../../includes/config.php';
$tool      = find_tool('voltage-drop-calculator');
$pageTitle = tr($tool['title']);
require __DIR__ . '/../../includes/header.php';

// Choices in the form. Key => text key in the language files.
$systems   = ['ac1' => 'vdrop.system.ac1', 'ac3' => 'vdrop.system.ac3', 'dc' => 'vdrop.system.dc'];
$materials = ['cu' => 'vdrop.material.cu', 'al' => 'vdrop.material.al'];
$units     = ['A', 'W', 'kW'];
$areas     = ['0.75', '1', '1.5', '2.5', '4', '6', '10', '16', '25', '35', '50', '70', '95', '120', '150', '185', '240'];
$temps     = ['20', '30', '70', '90'];

// Starting values: from the address (so a result can be shared) or an example.
function get_choice(string $name, array $allowed, string $default): string
{
    $value = $_GET[$name] ?? null;
    return is_string($value) && in_array($value, $allowed, true) ? $value : $default;
}
function get_text(string $name, string $default): string
{
    $value = $_GET[$name] ?? null;
    return is_string($value) && strlen($value) <= 20 ? trim($value) : $default;
}

$system   = get_choice('system', array_keys($systems), 'ac1');
$material = get_choice('material', array_keys($materials), 'cu');
$unit     = get_choice('unit', $units, 'A');
$area     = get_choice('area', $areas, '2.5');
$temp     = get_choice('temp', $temps, '70');
$voltage  = get_text('voltage', $system === 'ac3' ? '400' : ($system === 'dc' ? '12' : '230'));
$load     = get_text('load', '16');
$pf       = get_text('pf', '1');
$length   = get_text('length', '25');
$limit    = get_text('limit', '4');

// Result rows, filled in by the script. Key => text key in the language files.
$resultFields = ['current', 'resistance', 'drop', 'percent', 'end_voltage', 'loss', 'max_length', 'min_area'];

// Texts the script needs, in the current language.
$scriptTexts = [
    'ok'      => t('vdrop.status.ok'),
    'over'    => t('vdrop.status.over'),
    'none'    => t('vdrop.none'),
    'errors'  => [
        'voltage' => t('vdrop.error.voltage'),
        'load'    => t('vdrop.error.load'),
        'pf'      => t('vdrop.error.pf'),
        'length'  => t('vdrop.error.length'),
        'limit'   => t('vdrop.error.limit'),
    ],
];
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

    <noscript><p class="notice error"><?= e(t('vdrop.noscript')) ?></p></noscript>

    <div class="tool-layout">
        <section class="card" aria-labelledby="vdrop-form-title">
            <h2 id="vdrop-form-title"><?= e(t('vdrop.form_title')) ?></h2>
            <form id="vdrop-form" class="tool-form" action="/tools/voltage-drop-calculator" method="get" novalidate>
                <label>
                    <?= e(t('vdrop.system')) ?>
                    <select name="system">
                        <?php foreach ($systems as $value => $key): ?>
                            <option value="<?= e($value) ?>"<?= $value === $system ? ' selected' : '' ?>><?= e(t($key)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <?= e(t('vdrop.voltage')) ?>
                    <input type="text" name="voltage" value="<?= e($voltage) ?>" inputmode="decimal" autocomplete="off" aria-describedby="vdrop-voltage-hint">
                    <span id="vdrop-voltage-hint" class="form-hint"><?= e(t('vdrop.voltage_hint')) ?></span>
                </label>

                <div class="form-row">
                    <label>
                        <?= e(t('vdrop.load')) ?>
                        <input type="text" name="load" value="<?= e($load) ?>" inputmode="decimal" autocomplete="off">
                    </label>
                    <label>
                        <?= e(t('vdrop.unit')) ?>
                        <select name="unit">
                            <?php foreach ($units as $value): ?>
                                <option value="<?= e($value) ?>"<?= $value === $unit ? ' selected' : '' ?>><?= e($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <label data-ac-only>
                    <?= e(t('vdrop.pf')) ?>
                    <input type="text" name="pf" value="<?= e($pf) ?>" inputmode="decimal" autocomplete="off" aria-describedby="vdrop-pf-hint">
                    <span id="vdrop-pf-hint" class="form-hint"><?= e(t('vdrop.pf_hint')) ?></span>
                </label>

                <label>
                    <?= e(t('vdrop.length')) ?>
                    <input type="text" name="length" value="<?= e($length) ?>" inputmode="decimal" autocomplete="off" aria-describedby="vdrop-length-hint">
                    <span id="vdrop-length-hint" class="form-hint"><?= e(t('vdrop.length_hint')) ?></span>
                </label>

                <div class="form-row form-row-even">
                    <label>
                        <?= e(t('vdrop.area')) ?>
                        <select name="area">
                            <?php foreach ($areas as $value): ?>
                                <option value="<?= e($value) ?>"<?= $value === $area ? ' selected' : '' ?>><?= e(str_replace('.', t('vdrop.decimal'), $value)) ?> mm²</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <?= e(t('vdrop.material')) ?>
                        <select name="material">
                            <?php foreach ($materials as $value => $key): ?>
                                <option value="<?= e($value) ?>"<?= $value === $material ? ' selected' : '' ?>><?= e(t($key)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <label>
                    <?= e(t('vdrop.temp')) ?>
                    <select name="temp">
                        <?php foreach ($temps as $value): ?>
                            <option value="<?= e($value) ?>"<?= $value === $temp ? ' selected' : '' ?>><?= e(t("vdrop.temp.$value")) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <?= e(t('vdrop.limit')) ?>
                    <input type="text" name="limit" value="<?= e($limit) ?>" inputmode="decimal" autocomplete="off" aria-describedby="vdrop-limit-hint">
                    <span id="vdrop-limit-hint" class="form-hint"><?= e(t('vdrop.limit_hint')) ?></span>
                </label>

                <button type="submit" class="button button-primary"><?= icon('zap') ?> <?= e(t('vdrop.calculate')) ?></button>
            </form>
        </section>

        <section class="card" aria-labelledby="vdrop-results-title">
            <h2 id="vdrop-results-title"><?= e(t('vdrop.results')) ?></h2>
            <p id="vdrop-error" class="notice error" role="alert" hidden></p>

            <div id="vdrop-results" aria-live="polite" hidden>
                <p class="result-status" data-field="status"></p>
                <div class="result-meter" aria-hidden="true"><span data-field="meter"></span></div>

                <dl class="result-list">
                    <?php foreach ($resultFields as $field): ?>
                        <div>
                            <dt><?= e(t("vdrop.result.$field")) ?></dt>
                            <dd data-field="<?= e($field) ?>"></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
                <p class="form-hint"><?= e(t('vdrop.result_note')) ?></p>
            </div>
        </section>
    </div>

    <section class="prose tool-info">
        <h2><?= e(t('vdrop.howto_title')) ?></h2>
        <p><?= e(t('vdrop.howto1')) ?></p>
        <p><?= e(t('vdrop.howto2')) ?></p>
        <p><?= e(t('vdrop.howto3')) ?></p>
        <p><?= e(t('vdrop.howto4')) ?></p>
    </section>
</div>

<script type="application/json" id="vdrop-texts"><?= json_encode($scriptTexts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="/assets/js/tools/voltage-drop-calculator.js" defer></script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
