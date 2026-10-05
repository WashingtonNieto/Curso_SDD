import { el, icon } from './dom.js';
import { bindDialog, closeModal, openModal } from './modal.js';

let dialog = null;
let resolver = null;

function buildDialog() {
    const title = el('h2', { class: 'modal__title', id: 'confirm-dialog-title' });
    const message = el('p', { class: 'confirm-dialog__message' });
    const cancel = el('button', { type: 'button', class: 'btn btn--secondary', dataset: { modalClose: '' } }, 'Cancelar');
    const accept = el('button', { type: 'button', class: 'btn btn--danger' });

    dialog = el(
        'dialog',
        { class: 'modal modal--sm', 'aria-labelledby': 'confirm-dialog-title', dataset: { modal: '' } },
        el(
            'div',
            { class: 'modal__dialog' },
            el('header', { class: 'modal__header' }, title),
            el(
                'div',
                { class: 'modal__body confirm-dialog__body' },
                el('span', { class: 'confirm-dialog__icon' }, icon('fa-solid fa-triangle-exclamation')),
                message,
            ),
            el('footer', { class: 'modal__footer' }, cancel, accept),
        ),
    );

    accept.addEventListener('click', (event) => {
        event.preventDefault();
        settle(true);
    });

    dialog.addEventListener('close', () => settle(false));

    dialog.refs = { title, message, accept };
    document.body.append(dialog);
    bindDialog(dialog);
}

function settle(value) {
    if (resolver) {
        const resolve = resolver;
        resolver = null;
        closeModal(dialog);
        resolve(value);
    }
}

export function confirmAction({ title = '¿Estás segura?', message = '', confirmLabel = 'Confirmar' } = {}) {
    if (!dialog) {
        buildDialog();
    }

    dialog.refs.title.textContent = title;
    dialog.refs.message.textContent = message;
    dialog.refs.accept.textContent = confirmLabel;

    return new Promise((resolve) => {
        resolver = resolve;
        openModal(dialog);
        dialog.refs.accept.focus();
    });
}

export function initConfirmForms(root = document) {
    root.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-confirm]');

        if (!form || form.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();

        const accepted = await confirmAction({
            title: form.dataset.confirmTitle,
            message: form.dataset.confirm,
            confirmLabel: form.dataset.confirmLabel,
        });

        if (accepted) {
            form.dataset.confirmed = '1';
            form.requestSubmit();
            delete form.dataset.confirmed;
        }
    });
}
