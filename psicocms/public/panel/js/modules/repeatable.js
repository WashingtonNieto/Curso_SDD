export function initRepeatables(root = document) {
    root.querySelectorAll('[data-repeatable]').forEach((wrapper) => {
        const list = wrapper.querySelector('[data-repeatable-list]');
        const template = wrapper.querySelector('template[data-repeatable-template]');
        const addButton = wrapper.querySelector('[data-repeatable-add]');
        const max = Number(wrapper.dataset.max || 50);
        let nextIndex = Number(wrapper.dataset.nextIndex || list.children.length);

        const refresh = () => {
            addButton.hidden = list.children.length >= max;
        };

        addButton.addEventListener('click', (event) => {
            event.preventDefault();

            const fragment = template.content.cloneNode(true);
            fragment.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace('__INDEX__', String(nextIndex));
            });
            fragment.querySelectorAll('[id]').forEach((field) => {
                field.id = field.id.replace('__INDEX__', String(nextIndex));
            });
            fragment.querySelectorAll('label[for]').forEach((label) => {
                label.htmlFor = label.htmlFor.replace('__INDEX__', String(nextIndex));
            });

            nextIndex += 1;
            list.append(fragment);
            list.lastElementChild?.querySelector('input, textarea')?.focus();
            refresh();
        });

        list.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-repeatable-remove]');

            if (remove) {
                event.preventDefault();
                remove.closest('[data-repeatable-row]').remove();
                refresh();
            }
        });

        refresh();
    });
}
