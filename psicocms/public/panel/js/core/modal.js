export function openModal(modal) {
    const dialog = typeof modal === 'string' ? document.getElementById(modal) : modal;

    if (!dialog || dialog.open) {
        return dialog;
    }

    dialog.showModal();
    dialog.querySelector('[autofocus], input:not([type="hidden"]), select, textarea, .btn--primary, .btn--danger')?.focus();

    return dialog;
}

export function closeModal(modal) {
    const dialog = typeof modal === 'string' ? document.getElementById(modal) : modal;

    if (dialog?.open) {
        dialog.close();
    }
}

function bindDialog(dialog) {
    if (dialog.dataset.modalReady) {
        return;
    }

    dialog.dataset.modalReady = '1';

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-modal-close]')) {
            event.preventDefault();
            closeModal(dialog);
        }
    });
}

export function initModals(root = document) {
    root.querySelectorAll('dialog[data-modal]').forEach(bindDialog);

    root.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-modal-open]');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        const dialog = document.getElementById(trigger.dataset.modalOpen);

        if (dialog) {
            bindDialog(dialog);
            openModal(dialog);
        }
    });
}

export { bindDialog };
