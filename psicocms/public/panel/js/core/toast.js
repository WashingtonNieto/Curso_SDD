import { el, icon } from './dom.js';

const ICONS = {
    success: 'fa-solid fa-circle-check',
    error: 'fa-solid fa-circle-exclamation',
    info: 'fa-solid fa-circle-info',
};

function getStack() {
    let stack = document.querySelector('.toast-stack');

    if (!stack) {
        stack = el('div', { class: 'toast-stack', role: 'status', 'aria-live': 'polite' });
        document.body.append(stack);
    }

    return stack;
}

function dismiss(node) {
    node.classList.add('is-leaving');
    node.addEventListener('transitionend', () => node.remove(), { once: true });
    setTimeout(() => node.remove(), 400);
}

function bind(node, duration) {
    const close = node.querySelector('.toast__close');

    if (close) {
        close.addEventListener('click', (event) => {
            event.preventDefault();
            dismiss(node);
        });
    }

    if (duration > 0) {
        setTimeout(() => dismiss(node), duration);
    }
}

export function toast(message, type = 'success', duration = 5000) {
    const node = el(
        'div',
        { class: `toast toast--${type}` },
        icon(`toast__icon ${ICONS[type] ?? ICONS.info}`),
        el('p', { class: 'toast__message' }, message),
        el('button', { class: 'toast__close', type: 'button', 'aria-label': 'Cerrar aviso' }, icon('fa-solid fa-xmark')),
    );

    getStack().append(node);
    bind(node, duration);

    return node;
}

export function initToasts(duration = 6000) {
    document.querySelectorAll('.toast').forEach((node) => bind(node, duration));
}
