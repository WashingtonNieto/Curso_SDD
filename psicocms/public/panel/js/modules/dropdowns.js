function setExpanded(dropdown, expanded) {
    const toggle = dropdown.querySelector('[data-dropdown-toggle]');
    const menu = document.getElementById(toggle.getAttribute('aria-controls'));

    toggle.setAttribute('aria-expanded', String(expanded));

    if (menu) {
        menu.hidden = !expanded;
    }
}

function closeAll(except = null) {
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        if (dropdown !== except) {
            setExpanded(dropdown, false);
        }
    });
}

export function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-dropdown-toggle]');

        toggle.addEventListener('click', (event) => {
            event.preventDefault();

            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            closeAll(dropdown);
            setExpanded(dropdown, !expanded);

            if (!expanded) {
                document.getElementById(toggle.getAttribute('aria-controls'))?.querySelector('a, button')?.focus();
            }
        });

        dropdown.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                setExpanded(dropdown, false);
                toggle.focus();
            }
        });

        dropdown.addEventListener('focusout', (event) => {
            if (event.relatedTarget && !dropdown.contains(event.relatedTarget)) {
                setExpanded(dropdown, false);
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-dropdown]')) {
            closeAll();
        }
    });
}
