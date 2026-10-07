export function slugify(text) {
    return text
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 180)
        .replace(/-+$/g, '');
}

export function initSlugInputs(root = document) {
    root.querySelectorAll('[data-slug-target]').forEach((target) => {
        const source = document.getElementById(target.dataset.slugTarget);
        const preview = document.querySelector(`[data-slug-preview="${target.id}"]`);

        if (!source) {
            return;
        }

        let manual = 'slugLocked' in target.dataset || (target.value !== '' && target.value !== slugify(source.value));

        const updatePreview = () => {
            if (preview) {
                preview.textContent = target.value || slugify(source.value) || '…';
            }
        };

        source.addEventListener('input', () => {
            if (!manual) {
                target.value = slugify(source.value);
            }

            updatePreview();
        });

        target.addEventListener('input', () => {
            manual = target.value.trim() !== '';
            updatePreview();
        });

        target.addEventListener('blur', () => {
            target.value = slugify(target.value);

            if (target.value === '') {
                manual = false;
                target.value = slugify(source.value);
            }

            updatePreview();
        });

        updatePreview();
    });
}
