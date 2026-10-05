import { el } from '../core/dom.js';

const MAX_BYTES_DEFAULT = 4 * 1024 * 1024;

export function initImageUploads(root = document) {
    root.querySelectorAll('[data-image-upload]').forEach((wrapper) => {
        const input = wrapper.querySelector('input[type="file"]');
        const preview = wrapper.querySelector('[data-image-preview]');
        const fileName = wrapper.querySelector('[data-image-name]');
        const error = wrapper.querySelector('[data-image-error]');
        const maxBytes = Number(wrapper.dataset.maxBytes || MAX_BYTES_DEFAULT);

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', () => {
            const file = input.files[0];

            if (error) {
                error.hidden = true;
            }

            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/') || file.size > maxBytes) {
                input.value = '';

                if (error) {
                    error.textContent = file.size > maxBytes
                        ? `La imagen no puede pesar más de ${Math.round(maxBytes / 1024 / 1024)} MB.`
                        : 'El archivo elegido no es una imagen.';
                    error.hidden = false;
                }

                return;
            }

            preview.replaceChildren(el('img', { src: URL.createObjectURL(file), alt: 'Vista previa de la imagen elegida' }));

            if (fileName) {
                fileName.textContent = file.name;
            }
        });
    });
}
