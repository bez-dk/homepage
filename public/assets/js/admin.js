// Admin pages: ask before deleting, and warn before leaving a form with unsaved changes.

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('form[data-unsaved]').forEach((form) => {
    let changed = false;
    form.addEventListener('input', () => { changed = true; });
    form.addEventListener('change', () => { changed = true; });
    form.addEventListener('submit', () => { changed = false; });
    window.addEventListener('beforeunload', (event) => {
        if (changed) {
            event.preventDefault();
            event.returnValue = form.dataset.unsaved; // older browsers show a message instead of their own
        }
    });
});
