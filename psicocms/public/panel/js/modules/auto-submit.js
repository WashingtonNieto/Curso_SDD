export function initAutoSubmit(root = document) {
    root.addEventListener('change', (event) => {
        const field = event.target.closest('[data-auto-submit]');

        if (field?.form) {
            field.form.requestSubmit();
        }
    });
}
