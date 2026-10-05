import { el, icon } from '../core/dom.js';

function createChip(text, name) {
    return el(
        'span',
        { class: 'chip', dataset: { chip: text } },
        text,
        el('input', { type: 'hidden', name: `${name}[]`, value: text }),
        el('button', { type: 'button', class: 'chip__remove', 'aria-label': `Quitar ${text}`, dataset: { chipRemove: '' } }, icon('fa-solid fa-xmark')),
    );
}

export function initChipsInputs(root = document) {
    root.querySelectorAll('[data-chips]').forEach((wrapper) => {
        const input = wrapper.querySelector('[data-chips-input]');
        const addButton = wrapper.querySelector('[data-chips-add]');
        const list = wrapper.querySelector('[data-chips-list]');
        const name = wrapper.dataset.chips;

        const exists = (text) => [...list.querySelectorAll('[data-chip]')]
            .some((chip) => chip.dataset.chip.toLowerCase() === text.toLowerCase());

        const add = () => {
            const text = input.value.trim();

            if (text === '' || exists(text)) {
                input.value = '';
                input.focus();
                return;
            }

            list.append(createChip(text, name));
            input.value = '';
            input.focus();
        };

        addButton.addEventListener('click', (event) => {
            event.preventDefault();
            add();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                add();
            }
        });

        list.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-chip-remove]');

            if (remove) {
                event.preventDefault();
                remove.closest('[data-chip]').remove();
            }
        });
    });
}
