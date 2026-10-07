import { el, icon } from '../core/dom.js';
import { http } from '../core/http.js';
import { debounce } from '../core/debounce.js';
import { spinner } from '../core/loader.js';

const MIN_CHARS = 2;
const FIELDS = { firstName: 'first_name', lastName: 'last_name', phone: 'phone', email: 'email' };

function initAutocomplete(input) {
    const form = input.form;
    const wrapper = input.closest('.form-group');
    const listId = `${input.id}-sugerencias`;
    const list = el('ul', { class: 'autocomplete__list', id: listId, role: 'listbox', 'aria-label': 'Pacientes encontrados', hidden: true });
    const selected = el('p', { class: 'autocomplete__selected', hidden: true });

    let results = [];
    let activeIndex = -1;
    let requestId = 0;
    let filling = false;

    const anchor = input.closest('.input-group') ?? wrapper;
    anchor.classList.add('autocomplete');
    anchor.append(list);
    wrapper.append(selected);

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', listId);
    input.setAttribute('aria-expanded', 'false');

    const close = () => {
        list.hidden = true;
        activeIndex = -1;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    };

    const open = () => {
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const highlight = (index) => {
        const options = [...list.querySelectorAll('[role="option"]')];

        if (options.length === 0) {
            return;
        }

        activeIndex = (index + options.length) % options.length;
        options.forEach((option, position) => option.setAttribute('aria-selected', String(position === activeIndex)));
        input.setAttribute('aria-activedescendant', options[activeIndex].id);
        options[activeIndex].scrollIntoView({ block: 'nearest' });
    };

    const field = (name) => form.elements.namedItem(name);

    const choose = (patient) => {
        filling = true;

        Object.entries(FIELDS).forEach(([key, name]) => {
            const target = field(name);

            if (target) {
                target.value = patient[key] ?? '';
                target.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        filling = false;
        close();

        selected.replaceChildren(
            icon('fa-solid fa-circle-check'),
            el('span', {}, `Cita para ${patient.name} (${patient.phone}). Sus datos se han rellenado automáticamente.`),
        );
        selected.hidden = false;
    };

    const message = (text) => {
        list.replaceChildren(el('li', { class: 'autocomplete__message', role: 'presentation' }, text));
        open();
    };

    const render = () => {
        if (results.length === 0) {
            message('No hay pacientes con ese nombre o teléfono. Si continúas, se creará una ficha nueva al guardar la cita.');
            return;
        }

        list.replaceChildren(...results.map((patient, index) => {
            const option = el(
                'li',
                { class: 'autocomplete__option', id: `${listId}-${index}`, role: 'option', 'aria-selected': 'false' },
                el('span', { class: 'avatar avatar--sm', 'aria-hidden': 'true' }, patient.initials || '?'),
                el(
                    'span',
                    { class: 'autocomplete__text' },
                    el('strong', {}, patient.name),
                    el('span', { class: 'table__muted' }, [patient.phone, patient.email].filter(Boolean).join(' · ')),
                ),
            );

            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                choose(patient);
            });

            return option;
        }));

        open();
    };

    const search = debounce(async () => {
        const text = input.value.trim();
        const current = ++requestId;

        if (text.length < MIN_CHARS) {
            close();
            return;
        }

        list.replaceChildren(el('li', { class: 'autocomplete__message', role: 'presentation' }, spinner('Buscando pacientes…'), ' Buscando pacientes…'));
        open();

        try {
            const response = await http.get(`${input.dataset.patientAutocomplete}?${new URLSearchParams({ q: text })}`);

            if (current === requestId) {
                results = response.data;
                activeIndex = -1;
                render();
            }
        } catch (error) {
            if (current === requestId) {
                message(error.message);
            }
        }
    }, 300);

    input.addEventListener('input', () => {
        if (!filling) {
            search();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (list.hidden) {
            if (event.key === 'ArrowDown' && input.value.trim().length >= MIN_CHARS) {
                event.preventDefault();
                search();
            }

            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            highlight(activeIndex + (event.key === 'ArrowDown' ? 1 : -1));
        } else if (event.key === 'Enter' && activeIndex >= 0 && results[activeIndex]) {
            event.preventDefault();
            choose(results[activeIndex]);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            close();
        }
    });

    input.addEventListener('blur', () => close());

    [FIELDS.phone, FIELDS.firstName].forEach((name) => {
        field(name)?.addEventListener('input', () => {
            if (!filling) {
                selected.hidden = true;
            }
        });
    });
}

export function initPatientAutocomplete(root = document) {
    root.querySelectorAll('input[data-patient-autocomplete]').forEach(initAutocomplete);
}
