const MOBILE_QUERY = window.matchMedia('(max-width: 1024px)');

export function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');

    if (!sidebar) {
        return;
    }

    const overlay = document.querySelector('[data-sidebar-overlay]');
    const openButton = document.querySelector('[data-sidebar-open]');
    const closeButton = sidebar.querySelector('[data-sidebar-close]');

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        overlay.hidden = !open;
        openButton?.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';

        if (open) {
            closeButton?.focus();
        } else if (MOBILE_QUERY.matches) {
            openButton?.focus();
        }
    };

    openButton?.addEventListener('click', (event) => {
        event.preventDefault();
        setOpen(true);
    });

    closeButton?.addEventListener('click', (event) => {
        event.preventDefault();
        setOpen(false);
    });

    overlay?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
            setOpen(false);
        }
    });

    MOBILE_QUERY.addEventListener('change', (event) => {
        if (!event.matches && sidebar.classList.contains('is-open')) {
            setOpen(false);
        }
    });

    sidebar.querySelectorAll('[data-submenu-toggle]').forEach((toggle) => {
        const submenu = document.getElementById(toggle.getAttribute('aria-controls'));

        toggle.addEventListener('click', (event) => {
            event.preventDefault();

            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
            toggle.closest('.sidebar__group')?.classList.toggle('is-open', !expanded);

            if (submenu) {
                submenu.hidden = expanded;
            }
        });
    });
}
