function activate(container, tab, focus = false) {
    container.querySelectorAll('[role="tab"]').forEach((item) => {
        const selected = item === tab;
        item.setAttribute('aria-selected', String(selected));
        item.tabIndex = selected ? 0 : -1;
    });

    container.querySelectorAll('[data-tab-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.tabPanel !== tab.dataset.tab;
    });

    if (focus) {
        tab.focus();
    }

    container.dispatchEvent(new CustomEvent('tabs:change', { detail: { tab: tab.dataset.tab } }));
}

export function initTabs(root = document) {
    root.querySelectorAll('[data-tabs]').forEach((container) => {
        const tabs = [...container.querySelectorAll('[role="tab"]')];

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', (event) => {
                event.preventDefault();
                activate(container, tab);
            });

            tab.addEventListener('keydown', (event) => {
                const keys = { ArrowRight: 1, ArrowLeft: -1 };

                if (event.key in keys) {
                    event.preventDefault();
                    activate(container, tabs[(index + keys[event.key] + tabs.length) % tabs.length], true);
                } else if (event.key === 'Home' || event.key === 'End') {
                    event.preventDefault();
                    activate(container, event.key === 'Home' ? tabs[0] : tabs[tabs.length - 1], true);
                }
            });
        });
    });
}
