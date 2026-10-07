import { el } from '../core/dom.js';
import { http } from '../core/http.js';
import { closeModal } from '../core/modal.js';
import { toast } from '../core/toast.js';

function initQuickCategory() {
    const form = document.querySelector('[data-category-quick-form]');
    const select = document.querySelector('[data-category-select]');

    if (!form || !select) {
        return;
    }

    const modal = form.closest('dialog');
    const error = form.querySelector('[data-category-error]');
    const submit = form.querySelector('[type="submit"]');
    const nameInput = form.elements.namedItem('name');
    const descriptionInput = form.elements.namedItem('description');

    const showError = (message) => {
        error.textContent = message;
        error.hidden = !message;
        nameInput.classList.toggle('is-invalid', Boolean(message));
    };

    modal?.addEventListener('close', () => {
        form.reset();
        showError('');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        showError('');

        if (nameInput.value.trim() === '') {
            showError('Escribe el nombre de la categoría.');
            nameInput.focus();
            return;
        }

        submit.disabled = true;

        try {
            const { category, message } = await http.post(form.action, {
                name: nameInput.value,
                description: descriptionInput.value,
            });

            select.append(el('option', { value: String(category.id) }, category.name));
            select.value = String(category.id);
            closeModal(modal);
            toast(message);
        } catch (exception) {
            showError(exception.errors?.name?.[0] ?? exception.message);
        } finally {
            submit.disabled = false;
        }
    });
}

initQuickCategory();
