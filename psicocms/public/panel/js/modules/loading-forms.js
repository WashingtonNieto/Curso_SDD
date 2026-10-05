export function initLoadingForms(root = document) {
    root.querySelectorAll('form[data-loading-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[type="submit"][data-loading-text]');

            if (!button || button.classList.contains('is-loading')) {
                return;
            }

            const label = button.querySelector('.btn__label');
            const iconNode = button.querySelector('.btn__icon');

            button.classList.add('is-loading');
            button.setAttribute('aria-busy', 'true');

            if (label) {
                label.textContent = button.dataset.loadingText;
            }

            if (iconNode) {
                iconNode.className = 'btn__icon fa-solid fa-circle-notch';
            }
        });
    });
}
