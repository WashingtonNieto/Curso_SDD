import { toast } from '../core/toast.js';

export function initAppearance() {
    document.querySelectorAll('[data-appearance-toggle]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            toast('Muy pronto podrás elegir entre modo claro u oscuro y el color principal de tu panel.', 'info');
        });
    });
}
