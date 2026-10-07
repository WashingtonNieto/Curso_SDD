import { el } from '../core/dom.js';

const WARNING_RATIO = 0.9;

export function initCharCounters(root = document) {
    root.querySelectorAll('[data-char-counter]').forEach((field) => {
        const max = Number(field.getAttribute('maxlength') || field.dataset.charCounter || 0);

        if (!max || field.dataset.charCounterReady) {
            return;
        }

        field.dataset.charCounterReady = '1';

        const counter = el('p', { class: 'char-counter', 'aria-live': 'polite' });
        (field.closest('.input-group') ?? field).after(counter);

        const update = () => {
            const length = field.value.length;
            counter.textContent = `${length} / ${max} caracteres`;
            counter.classList.toggle('char-counter--warning', length >= max * WARNING_RATIO);
        };

        field.addEventListener('input', update);
        update();
    });
}
