// IP subnet calculator. Everything is calculated here in the browser,
// the page (tools/ip-subnet-calculator.php) only provides the form and texts.

// Special IPv4 ranges, checked in order: [first address, prefix, type].
const ADDRESS_RANGES = [
    ['255.255.255.255', 32, 'broadcast'],
    ['0.0.0.0', 8, 'this_network'],
    ['10.0.0.0', 8, 'private'],
    ['100.64.0.0', 10, 'shared'],
    ['127.0.0.0', 8, 'loopback'],
    ['169.254.0.0', 16, 'link_local'],
    ['172.16.0.0', 12, 'private'],
    ['192.0.2.0', 24, 'documentation'],
    ['192.168.0.0', 16, 'private'],
    ['198.51.100.0', 24, 'documentation'],
    ['203.0.113.0', 24, 'documentation'],
    ['224.0.0.0', 4, 'multicast'],
    ['240.0.0.0', 4, 'reserved'],
];

// "192.168.1.10" -> 3232235786, or null if it isn't a valid IPv4 address.
function parseIp(text) {
    const parts = text.split('.');
    if (parts.length !== 4 || !parts.every((part) => /^\d{1,3}$/.test(part) && Number(part) <= 255)) {
        return null;
    }
    return parts.reduce((ip, part) => ip * 256 + Number(part), 0);
}

// 3232235786 -> "192.168.1.10"
function formatIp(ip) {
    return [24, 16, 8, 0].map((shift) => (ip >>> shift) & 255).join('.');
}

// The subnet mask for a prefix, e.g. 24 -> 255.255.255.0 (as a number).
function prefixToMask(prefix) {
    // Shifting by 32 does nothing in JavaScript, so /0 needs its own case.
    return prefix === 0 ? 0 : (0xFFFFFFFF << (32 - prefix)) >>> 0;
}

function addressType(ip) {
    const range = ADDRESS_RANGES.find(([start, prefix]) => {
        const mask = prefixToMask(prefix);
        return ((ip & mask) >>> 0) === parseIp(start);
    });
    return range ? range[2] : 'public';
}

// The old class system (A–E), from the first bits of the address.
function addressClass(ip) {
    const first = ip >>> 24;
    if (first < 128) return 'A';
    if (first < 192) return 'B';
    if (first < 224) return 'C';
    if (first < 240) return 'D';
    return 'E';
}

function calculate(ip, prefix) {
    const mask      = prefixToMask(prefix);
    const wildcard  = ~mask >>> 0;
    const network   = (ip & mask) >>> 0;
    const broadcast = (network | wildcard) >>> 0;
    const total     = 2 ** (32 - prefix);

    // /31 (point-to-point) and /32 (single host) have no network or broadcast address to set aside.
    const small = prefix >= 31;

    return {
        ip, prefix, mask, wildcard, network, total,
        broadcast: small ? null : broadcast,
        first:     small ? network : network + 1,
        last:      small ? broadcast : broadcast - 1,
        hosts:     small ? total : total - 2,
        class:     addressClass(ip),
        type:      addressType(ip),
    };
}

// 32 bits as "11000000.10101000.00000001.00001010", with the network part highlighted.
function renderBinary(element, value, prefix) {
    const bits = value.toString(2).padStart(32, '0').replace(/(.{8})(?!$)/g, '$1.');
    // Each dot added before position n shifts the split point by one.
    const split = prefix + Math.floor(Math.max(prefix - 1, 0) / 8);
    const networkPart = document.createElement('span');
    networkPart.className = 'bits-network';
    networkPart.textContent = bits.slice(0, split);
    element.replaceChildren(networkPart, bits.slice(split));
}

const form = document.querySelector('#subnet-form');

if (form) {
    const texts      = JSON.parse(document.querySelector('#subnet-texts').textContent);
    const results    = document.querySelector('#subnet-results');
    const errorBox   = document.querySelector('#subnet-error');
    const note       = results.querySelector('[data-field="note"]');
    const ipInput    = form.elements.ip;
    const prefixList = form.elements.prefix;
    const numbers    = new Intl.NumberFormat(document.documentElement.lang);

    function showError(message) {
        errorBox.textContent = message;
        errorBox.hidden = false;
        results.hidden = true;
    }

    function update() {
        // Allow "192.168.1.10/24" in the address field; the prefix then sets the dropdown.
        const [address, prefixText] = ipInput.value.trim().split('/');
        if (prefixText) {
            if (!/^\d{1,2}$/.test(prefixText) || Number(prefixText) > 32) {
                showError(texts.errors.prefix);
                return;
            }
            prefixList.value = prefixText;
        }

        const ip = parseIp(address);
        if (ip === null) {
            showError(texts.errors.ip);
            return;
        }

        const prefix = Number(prefixList.value);
        const result = calculate(ip, prefix);

        const values = {
            address:   formatIp(result.ip),
            cidr:      `${formatIp(result.network)}/${prefix}`,
            network:   formatIp(result.network),
            broadcast: result.broadcast === null ? texts.none : formatIp(result.broadcast),
            mask:      formatIp(result.mask),
            wildcard:  formatIp(result.wildcard),
            first:     formatIp(result.first),
            last:      formatIp(result.last),
            hosts:     numbers.format(result.hosts),
            total:     numbers.format(result.total),
            class:     result.class,
            type:      texts.types[result.type],
        };
        for (const [field, value] of Object.entries(values)) {
            results.querySelector(`[data-field="${field}"]`).textContent = value;
        }

        note.textContent = prefix === 31 ? texts.note31 : prefix === 32 ? texts.note32 : '';
        note.hidden = prefix < 31;

        const binary = { address: result.ip, mask: result.mask, network: result.network };
        for (const [field, value] of Object.entries(binary)) {
            renderBinary(results.querySelector(`[data-binary="${field}"]`), value, prefix);
        }

        errorBox.hidden = true;
        results.hidden = false;

        // Keep the address bar in step, so the result can be bookmarked or shared.
        const url = new URL(window.location);
        url.searchParams.set('ip', address);
        url.searchParams.set('prefix', prefix);
        history.replaceState(null, '', url);
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        update();
    });
    prefixList.addEventListener('change', update);
    ipInput.addEventListener('input', () => {
        // Recalculate while typing, but wait with errors until the address looks finished.
        if (parseIp(ipInput.value.trim().split('/')[0]) !== null) {
            update();
        }
    });

    update();
}
