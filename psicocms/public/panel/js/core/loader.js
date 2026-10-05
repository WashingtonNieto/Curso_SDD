import { el } from './dom.js';

export function showTableSkeleton(tbody, columns, rows = 5) {
    const skeletonRows = Array.from({ length: rows }, () => el(
        'tr',
        { class: 'table__skeleton-row', 'aria-hidden': 'true' },
        Array.from({ length: columns }, (_, index) => el('td', {}, el('span', { class: 'skeleton', style: `width: ${index === 0 ? 80 : 55}%` }))),
    ));

    tbody.closest('table')?.setAttribute('aria-busy', 'true');
    tbody.replaceChildren(...skeletonRows);
}

export function hideTableSkeleton(tbody) {
    tbody.closest('table')?.removeAttribute('aria-busy');
}

export function setBusy(element, busy = true) {
    element.classList.toggle('is-busy', busy);
    element.setAttribute('aria-busy', String(busy));
}

export function spinner(label = 'Cargando…') {
    return el('span', { class: 'spinner', role: 'status', 'aria-label': label });
}
