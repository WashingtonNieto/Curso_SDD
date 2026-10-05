export function el(tag, attrs = {}, ...children) {
    const node = document.createElement(tag);

    Object.entries(attrs).forEach(([key, value]) => {
        if (value === null || value === undefined || value === false) {
            return;
        }

        if (key === 'class') {
            node.className = value;
        } else if (key === 'dataset') {
            Object.assign(node.dataset, value);
        } else if (key.startsWith('on') && typeof value === 'function') {
            node.addEventListener(key.slice(2).toLowerCase(), value);
        } else if (value === true) {
            node.setAttribute(key, '');
        } else {
            node.setAttribute(key, value);
        }
    });

    node.append(...children.flat().filter((child) => child !== null && child !== undefined && child !== false));

    return node;
}

export function icon(className) {
    return el('i', { class: className, 'aria-hidden': 'true' });
}

export function clear(node) {
    node.replaceChildren();
}
