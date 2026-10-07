import { el, icon } from '../core/dom.js';
import { http } from '../core/http.js';
import { hideTableSkeleton, showTableSkeleton } from '../core/loader.js';
import { debounce } from '../core/debounce.js';
import { confirmAction } from '../core/confirm.js';
import { toast } from '../core/toast.js';

const COLUMNS = 6;
const MODALITY_ICONS = { online: 'fa-solid fa-video', presencial: 'fa-solid fa-location-dot' };
const STATUS_TONES = { activo: 'success', pausado: 'warning', alta: 'neutral' };

function badge(tone, label, iconClass = null) {
    return el('span', { class: `badge badge--${tone}` }, iconClass ? icon(`badge__icon ${iconClass}`) : null, label);
}

function button(label, iconClass, attrs = {}) {
    return el('button', { type: 'button', class: 'btn btn--secondary', ...attrs }, icon(`btn__icon ${iconClass}`), el('span', { class: 'btn__label' }, label));
}

function actionLink(href, iconClass, label, title) {
    return el('a', { class: 'btn btn--icon btn--ghost', href, 'aria-label': label, title }, icon(iconClass));
}

function pageNumbers(current, last) {
    const pages = [];

    for (let page = 1; page <= last; page += 1) {
        if (page === 1 || page === last || Math.abs(page - current) <= 1) {
            pages.push(page);
        } else if (pages[pages.length - 1] !== '…') {
            pages.push('…');
        }
    }

    return pages;
}

export function initPatientsTable(root) {
    const form = root.querySelector('[data-patients-filters]');
    const body = root.querySelector('[data-patients-body]');
    const tableWrap = root.querySelector('[data-patients-table]');
    const empty = root.querySelector('[data-patients-empty]');
    const pagination = root.querySelector('[data-patients-pagination]');
    const resetLink = form.querySelector('[data-filters-reset]');

    let page = Number(new URLSearchParams(window.location.search).get('page')) || 1;
    let requestId = 0;

    const filters = () => {
        const params = new URLSearchParams();

        new FormData(form).forEach((value, key) => {
            const clean = String(value).trim();

            if (clean !== '') {
                params.set(key, clean);
            }
        });

        return params;
    };

    const syncUrl = (params) => {
        const query = new URLSearchParams(params);

        if (page > 1) {
            query.set('page', String(page));
        }

        const search = query.toString();
        window.history.replaceState(null, '', search ? `${window.location.pathname}?${search}` : window.location.pathname);

        if (resetLink) {
            resetLink.hidden = [...params.keys()].length === 0;
        }
    };

    const updateStats = (stats = {}) => {
        Object.entries(stats).forEach(([key, value]) => {
            const target = document.querySelector(`[data-stat="${key}"] .stat-card__value`);

            if (target) {
                target.textContent = String(value);
            }
        });
    };

    const clearFilters = () => {
        form.querySelectorAll('input, select').forEach((field) => {
            field.value = '';
        });
        page = 1;
        load();
    };

    const goTo = (target) => {
        page = target;
        load();
        tableWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const remove = async (patient, trigger) => {
        const accepted = await confirmAction({
            title: '¿Eliminar la ficha de este paciente?',
            message: patient.deleteMessage,
            confirmLabel: 'Sí, eliminar',
        });

        if (!accepted) {
            return;
        }

        trigger.disabled = true;

        try {
            const response = await http.delete(patient.urls.destroy);
            toast(response.message);
            load();
        } catch (error) {
            toast(error.message, 'error');
            trigger.disabled = false;
        }
    };

    const row = (patient) => {
        const next = patient.nextAppointment
            ? el('td', {}, el('strong', {}, patient.nextAppointment.date), el('span', { class: 'table__muted' }, patient.nextAppointment.time))
            : el('td', {}, el('em', { class: 'patients-table__none' }, 'Sin programar'));

        const deleteButton = el(
            'button',
            { type: 'button', class: 'btn btn--icon btn--ghost-danger', 'aria-label': `Eliminar la ficha de ${patient.name}`, title: 'Eliminar' },
            icon('fa-regular fa-trash-can'),
        );
        deleteButton.addEventListener('click', (event) => {
            event.preventDefault();
            remove(patient, deleteButton);
        });

        return el(
            'tr',
            {},
            el(
                'td',
                {},
                el(
                    'div',
                    { class: 'table__person' },
                    el('span', { class: 'avatar avatar--sm', 'aria-hidden': 'true' }, patient.initials || '?'),
                    el(
                        'span',
                        { class: 'patients-table__name' },
                        el('a', { class: 'patients-table__link', href: patient.urls.show }, patient.name),
                        el('span', { class: 'table__muted' }, patient.phone),
                        patient.subtitle ? el('span', { class: 'table__muted patients-table__subtitle' }, patient.subtitle) : null,
                    ),
                ),
            ),
            el('td', {}, patient.lastAppointment ?? el('span', { class: 'table__muted' }, '—')),
            next,
            el('td', {}, patient.modality ? badge(patient.modality, patient.modalityLabel, MODALITY_ICONS[patient.modality]) : el('span', { class: 'table__muted' }, '—')),
            el('td', {}, badge(STATUS_TONES[patient.status] ?? 'neutral', patient.statusLabel)),
            el(
                'td',
                {},
                el(
                    'div',
                    { class: 'table__actions' },
                    actionLink(patient.urls.show, 'fa-regular fa-eye', `Ver la ficha de ${patient.name}`, 'Ver ficha'),
                    actionLink(patient.urls.appointment, 'fa-regular fa-calendar-plus', `Nueva cita para ${patient.name}`, 'Nueva cita'),
                    actionLink(patient.urls.edit, 'fa-regular fa-pen-to-square', `Editar los datos de ${patient.name}`, 'Editar'),
                    deleteButton,
                ),
            ),
        );
    };

    const pageLink = (target, content, disabled, label = null) => {
        if (disabled) {
            return el('span', { class: 'pagination__link is-disabled', 'aria-disabled': 'true' }, ...content);
        }

        const link = el('a', { class: 'pagination__link', href: `?page=${target}`, 'aria-label': label }, ...content);
        link.addEventListener('click', (event) => {
            event.preventDefault();
            goTo(target);
        });

        return link;
    };

    const renderPagination = (meta) => {
        const nav = el('nav', { class: 'pagination', 'aria-label': 'Paginación' }, el('p', { class: 'pagination__info' }, `Mostrando ${meta.from}–${meta.to} de ${meta.total} pacientes`));

        if (meta.lastPage > 1) {
            const list = el('ul', { class: 'pagination__list' });
            list.append(el('li', {}, pageLink(meta.page - 1, [icon('fa-solid fa-chevron-left'), ' Anterior'], meta.page === 1)));

            pageNumbers(meta.page, meta.lastPage).forEach((number) => {
                if (number === '…') {
                    list.append(el('li', {}, el('span', { class: 'pagination__dots', 'aria-hidden': 'true' }, '…')));
                } else if (number === meta.page) {
                    list.append(el('li', { class: 'pagination__page' }, el('span', { class: 'pagination__link is-current', 'aria-current': 'page' }, String(number))));
                } else {
                    list.append(el('li', { class: 'pagination__page' }, pageLink(number, [String(number)], false, `Ir a la página ${number}`)));
                }
            });

            list.append(el('li', {}, pageLink(meta.page + 1, ['Siguiente ', icon('fa-solid fa-chevron-right')], meta.page === meta.lastPage)));
            nav.append(list);
        }

        pagination.replaceChildren(nav);
    };

    const renderEmpty = (filtered) => {
        const node = filtered
            ? el(
                'div',
                { class: 'empty-state' },
                el('span', { class: 'empty-state__icon' }, icon('fa-solid fa-filter-circle-xmark')),
                el('h2', { class: 'empty-state__title' }, 'No hay pacientes con estos filtros'),
                el('p', { class: 'empty-state__text' }, 'Prueba con otro nombre o teléfono, o quita algún filtro.'),
                el('div', { class: 'empty-state__actions' }, button('Quitar filtros', 'fa-solid fa-rotate-left', { onClick: clearFilters })),
            )
            : el(
                'div',
                { class: 'empty-state' },
                el('span', { class: 'empty-state__icon' }, icon('fa-solid fa-user-group')),
                el('h2', { class: 'empty-state__title' }, 'Aún no tienes pacientes'),
                el('p', { class: 'empty-state__text' }, 'Se crearán solos cuando alguien reserve desde tu web o cuando añadas una cita.'),
                el(
                    'div',
                    { class: 'empty-state__actions' },
                    el('a', { class: 'btn btn--primary', href: root.dataset.createUrl }, icon('btn__icon fa-solid fa-user-plus'), el('span', { class: 'btn__label' }, 'Añadir paciente')),
                ),
            );

        tableWrap.hidden = true;
        pagination.replaceChildren();
        empty.replaceChildren(node);
        empty.hidden = false;
    };

    const renderError = (message) => {
        body.replaceChildren(el(
            'tr',
            {},
            el(
                'td',
                { colspan: String(COLUMNS) },
                el(
                    'div',
                    { class: 'patients-table__error', role: 'alert' },
                    icon('fa-solid fa-circle-exclamation'),
                    el('span', {}, message),
                    button('Reintentar', 'fa-solid fa-rotate-right', { onClick: () => load() }),
                ),
            ),
        ));
    };

    async function load() {
        const params = filters();
        const current = ++requestId;
        const query = new URLSearchParams(params);
        query.set('page', String(page));

        syncUrl(params);
        empty.hidden = true;
        tableWrap.hidden = false;
        showTableSkeleton(body, COLUMNS, 5);

        try {
            const response = await http.get(`${root.dataset.listUrl}?${query}`);

            if (current !== requestId) {
                return;
            }

            if (response.data.length === 0 && response.meta.total > 0 && page > response.meta.lastPage) {
                page = response.meta.lastPage;
                load();
                return;
            }

            updateStats(response.stats);

            if (response.data.length === 0) {
                renderEmpty(response.filtered);
                return;
            }

            body.replaceChildren(...response.data.map(row));
            renderPagination(response.meta);
        } catch (error) {
            if (current === requestId) {
                pagination.replaceChildren();
                renderError(error.message);
            }
        } finally {
            if (current === requestId) {
                hideTableSkeleton(body);
            }
        }
    }

    const reload = () => {
        page = 1;
        load();
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        reload();
    });

    form.querySelector('input[name="q"]')?.addEventListener('input', debounce(reload, 300));
    form.querySelectorAll('select').forEach((select) => select.addEventListener('change', reload));

    resetLink?.addEventListener('click', (event) => {
        event.preventDefault();
        clearFilters();
    });

    load();
}
