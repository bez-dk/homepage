// Mobile menu toggle
const toggle = document.querySelector('.nav-toggle');

if (toggle) {
    toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('nav-open', open);
    });
}

// Dropdowns (<details class="dropdown">): close when clicking outside or pressing Escape
const dropdowns = document.querySelectorAll('details.dropdown');

document.addEventListener('click', (event) => {
    dropdowns.forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.open = false;
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        dropdowns.forEach((dropdown) => {
            if (dropdown.open) {
                dropdown.open = false;
                dropdown.querySelector('summary').focus();
            }
        });
    }
});

// Light / dark theme toggle (the starting theme is set in the page <head>)
const themeToggle = document.querySelector('.theme-toggle');

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const root = document.documentElement;
        const theme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', theme);
        try { localStorage.setItem('theme', theme); } catch (e) {}
    });
}
