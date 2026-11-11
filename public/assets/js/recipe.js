// Recipe page: print button and the servings calculator, which scales the
// amounts at the start of each ingredient (marked <span class="qty"> by the page).

document.querySelector('[data-print]')?.addEventListener('click', () => window.print());

const servings = document.querySelector('[data-servings]');

if (servings) {
    const original = Number(servings.dataset.servings);
    const output   = servings.querySelector('output');
    const amounts  = [...document.querySelectorAll('.qty')].map((element) => ({
        element,
        text: element.textContent,
        min:  Number(element.dataset.qty),
        max:  element.dataset.qtyMax ? Number(element.dataset.qtyMax) : null,
    }));
    const numbers = new Intl.NumberFormat(document.documentElement.lang, { maximumFractionDigits: 1 });
    const FRACTIONS = [[0.25, '¼'], [1 / 3, '⅓'], [0.5, '½'], [2 / 3, '⅔'], [0.75, '¾']];
    let count = original;

    // 1.5 -> "1½", 0.333 -> "⅓", 2.4 -> "2,4" (Danish) / "2.4", 467 -> "467"
    function format(value) {
        if (value < 10) {
            const whole = Math.floor(value);
            const fraction = FRACTIONS.find(([part]) => Math.abs(value - whole - part) < 0.02);
            if (fraction) return (whole || '') + fraction[1];
        }
        return numbers.format(value >= 10 ? Math.round(value) : value);
    }

    function update() {
        output.textContent = count;
        const factor = count / original;
        for (const amount of amounts) {
            amount.element.textContent = count === original
                ? amount.text
                : format(amount.min * factor) + (amount.max === null ? '' : '–' + format(amount.max * factor));
        }
        servings.querySelector('[data-step="-1"]').disabled = count <= 1;
    }

    servings.addEventListener('click', (event) => {
        const button = event.target.closest('[data-step]');
        if (button) {
            count = Math.min(99, Math.max(1, count + Number(button.dataset.step)));
            update();
        }
    });

    // Only shown when there are amounts to scale.
    servings.hidden = amounts.length === 0;
    update();
}
