// Cable voltage drop calculator. Everything is calculated here in the browser,
// the page (tools/voltage-drop-calculator.php) only provides the form and texts.

// Resistivity at 20 °C in Ω·mm²/m and how much it rises per °C.
const MATERIALS = {
    cu: { resistivity: 0.0178, alpha: 0.00393 },
    al: { resistivity: 0.0286, alpha: 0.00403 },
};

// Reactance of the cable per metre (Ω/m) for AC, the usual value from IEC/HD 60364-5-52 annex G.
const REACTANCE = 0.08e-3;

// Standard cross-sections in mm², the same list as in the form.
const AREAS = [0.75, 1, 1.5, 2.5, 4, 6, 10, 16, 25, 35, 50, 70, 95, 120, 150, 185, 240];

// Typical voltage per system, used when the system is changed.
const DEFAULT_VOLTAGE = { ac1: '230', ac3: '400', dc: '12' };

// "2,5" or "2.5" -> 2.5, or NaN if it isn't a number.
function parseNumber(text) {
    const cleaned = text.trim().replace(',', '.');
    return /^\d*\.?\d+$|^\d+\.$/.test(cleaned) ? Number(cleaned) : NaN;
}

// Voltage drop in volts for a given cross-section (the rest of the input stays the same).
function dropFor(input, area) {
    const { material, temp, length, current, system, pf } = input;
    const { resistivity, alpha } = MATERIALS[material];
    const r = resistivity * (1 + alpha * (temp - 20)) / area; // Ω per metre of conductor
    const x = system === 'dc' ? 0 : REACTANCE;
    const sin = Math.sqrt(1 - pf * pf);
    // Single-phase and DC: current goes out and back (2 conductors). Three-phase: √3 × line voltage.
    const factor = system === 'ac3' ? Math.sqrt(3) : 2;
    return { r, perMetre: factor * current * (r * pf + x * sin) };
}

function calculate(input) {
    const { system, voltage, load, unit, length, limit, area } = input;
    const pf = system === 'dc' ? 1 : input.pf;

    // The load as a current in amperes.
    const watts = unit === 'kW' ? load * 1000 : load;
    let current = load;
    if (unit !== 'A') {
        current = system === 'ac3' ? watts / (Math.sqrt(3) * voltage * pf)
                : system === 'ac1' ? watts / (voltage * pf)
                : watts / voltage;
    }

    const full = { ...input, pf, current };
    const { r, perMetre } = dropFor(full, area);
    const drop = perMetre * length;
    const limitVolts = voltage * limit / 100;

    // Smallest standard cross-section that stays within the limit.
    const minArea = AREAS.find((a) => dropFor(full, a).perMetre * length <= limitVolts);

    return {
        current,
        resistance: r * length,
        drop,
        percent: drop / voltage * 100,
        endVoltage: voltage - drop,
        loss: (system === 'ac3' ? 3 : 2) * current * current * r * length,
        maxLength: perMetre > 0 ? limitVolts / perMetre : Infinity,
        minArea,
        withinLimit: drop <= limitVolts,
    };
}

const form = document.querySelector('#vdrop-form');

if (form) {
    const texts    = JSON.parse(document.querySelector('#vdrop-texts').textContent);
    const results  = document.querySelector('#vdrop-results');
    const errorBox = document.querySelector('#vdrop-error');
    const status   = results.querySelector('[data-field="status"]');
    const meter    = results.querySelector('[data-field="meter"]');
    const acOnly   = form.querySelector('[data-ac-only]');
    const lang     = document.documentElement.lang;
    const fields   = form.elements;

    // Format with the decimals that make sense for the size of the number.
    const format = (value, decimals) =>
        new Intl.NumberFormat(lang, { maximumFractionDigits: decimals, minimumFractionDigits: decimals }).format(value);

    function showError(message) {
        errorBox.textContent = message;
        errorBox.hidden = false;
        results.hidden = true;
    }

    function readInput() {
        const input = {
            system:   fields.system.value,
            unit:     fields.unit.value,
            material: fields.material.value,
            area:     Number(fields.area.value),
            temp:     Number(fields.temp.value),
            voltage:  parseNumber(fields.voltage.value),
            load:     parseNumber(fields.load.value),
            pf:       parseNumber(fields.pf.value),
            length:   parseNumber(fields.length.value),
            limit:    parseNumber(fields.limit.value),
        };
        // Check each number, in the order they appear in the form.
        if (!(input.voltage > 0)) return { error: 'voltage' };
        if (!(input.load > 0)) return { error: 'load' };
        if (input.system !== 'dc' && !(input.pf > 0 && input.pf <= 1)) return { error: 'pf' };
        if (!(input.length > 0)) return { error: 'length' };
        if (!(input.limit > 0 && input.limit < 100)) return { error: 'limit' };
        return input;
    }

    function update() {
        acOnly.hidden = fields.system.value === 'dc';

        const input = readInput();
        if (input.error) {
            showError(texts.errors[input.error]);
            return;
        }

        const result = calculate(input);

        const values = {
            current:     `${format(result.current, 2)} A`,
            resistance:  `${format(result.resistance, 4)} Ω`,
            drop:        `${format(result.drop, 2)} V`,
            percent:     `${format(result.percent, 2)} %`,
            end_voltage: `${format(result.endVoltage, 1)} V`,
            loss:        `${format(result.loss, 1)} W`,
            max_length:  Number.isFinite(result.maxLength) ? `${format(result.maxLength, 0)} m` : '∞',
            min_area:    result.minArea ? `${format(result.minArea, result.minArea < 1 ? 2 : result.minArea % 1 ? 1 : 0)} mm²` : texts.none,
        };
        for (const [field, value] of Object.entries(values)) {
            results.querySelector(`[data-field="${field}"]`).textContent = value;
        }

        // Status line and a bar showing how much of the allowed drop is used.
        const used = result.percent / input.limit;
        status.textContent = (result.withinLimit ? texts.ok : texts.over)
            .replace('{percent}', format(result.percent, 2))
            .replace('{limit}', format(input.limit, input.limit % 1 ? 1 : 0));
        status.classList.toggle('is-over', !result.withinLimit);
        meter.style.width = `${Math.min(used, 1) * 100}%`;
        meter.classList.toggle('is-over', !result.withinLimit);

        errorBox.hidden = true;
        results.hidden = false;

        // Keep the address bar in step, so the result can be bookmarked or shared.
        const url = new URL(window.location);
        for (const name of ['system', 'voltage', 'load', 'unit', 'pf', 'length', 'area', 'material', 'temp', 'limit']) {
            url.searchParams.set(name, fields[name].value.trim());
        }
        history.replaceState(null, '', url);
    }

    // When the system changes, swap in its usual voltage unless the user typed their own.
    let previousSystem = fields.system.value;
    fields.system.addEventListener('change', () => {
        if (fields.voltage.value.trim() === DEFAULT_VOLTAGE[previousSystem]) {
            fields.voltage.value = DEFAULT_VOLTAGE[fields.system.value];
        }
        previousSystem = fields.system.value;
        update();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        update();
    });
    form.addEventListener('change', (event) => {
        if (event.target.tagName === 'SELECT' && event.target !== fields.system) update();
    });
    form.addEventListener('input', (event) => {
        // Recalculate while typing, but wait with errors until the form is valid again.
        if (event.target.tagName === 'INPUT' && !readInput().error) update();
    });

    update();
}
