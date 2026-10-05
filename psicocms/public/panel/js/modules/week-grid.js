import { http } from '../core/http.js';
import { toast } from '../core/toast.js';

const WORKDAYS = ['2', '3', '4', '5'];
const MODALITY_LABELS = { online: 'Online', presencial: 'Presencial' };

function updateReviewBanner(pending) {
    const banner = document.querySelector('[data-review-banner]');

    if (!banner) {
        return;
    }

    banner.hidden = pending.length === 0;
    const list = banner.querySelector('[data-review-list]');

    if (list) {
        list.textContent = pending.map((modality) => MODALITY_LABELS[modality] ?? modality).join(' y ');
    }
}

function initGrid(grid) {
    const cells = [...grid.querySelectorAll('[data-weekday][data-time]')];
    const counter = grid.querySelector('[data-grid-count]');
    const dirtyLabel = grid.querySelector('[data-grid-dirty]');
    const saveButton = grid.querySelector('[data-grid-save]');
    const saveLabel = saveButton.querySelector('.btn__label');

    const isOn = (cell) => cell.getAttribute('aria-pressed') === 'true';
    const set = (cell, on) => cell.setAttribute('aria-pressed', String(on));
    const snapshot = () => cells.filter(isOn).map((cell) => `${cell.dataset.weekday}-${cell.dataset.time}`).join('|');
    let saved = snapshot();

    const refresh = () => {
        counter.textContent = String(cells.filter(isOn).length);
        dirtyLabel.hidden = snapshot() === saved;
    };

    const toggleGroup = (group) => {
        const turnOn = !group.every(isOn);
        group.forEach((cell) => set(cell, turnOn));
        refresh();
    };

    grid.addEventListener('click', (event) => {
        const cell = event.target.closest('[data-weekday][data-time]');
        const day = event.target.closest('[data-toggle-day]');
        const time = event.target.closest('[data-toggle-time]');

        if (cell) {
            event.preventDefault();
            set(cell, !isOn(cell));
            refresh();
        } else if (day) {
            event.preventDefault();
            toggleGroup(cells.filter((item) => item.dataset.weekday === day.dataset.toggleDay));
        } else if (time) {
            event.preventDefault();
            toggleGroup(cells.filter((item) => item.dataset.time === time.dataset.toggleTime));
        }
    });

    grid.querySelector('[data-grid-copy-monday]').addEventListener('click', (event) => {
        event.preventDefault();

        const mondayOn = new Set(cells.filter((cell) => cell.dataset.weekday === '1' && isOn(cell)).map((cell) => cell.dataset.time));
        cells.filter((cell) => WORKDAYS.includes(cell.dataset.weekday)).forEach((cell) => set(cell, mondayOn.has(cell.dataset.time)));
        refresh();
        toast('Horario del lunes copiado de martes a viernes. Recuerda guardar los cambios.', 'info');
    });

    grid.querySelector('[data-grid-clear]').addEventListener('click', (event) => {
        event.preventDefault();
        cells.forEach((cell) => set(cell, false));
        refresh();
    });

    saveButton.addEventListener('click', async (event) => {
        event.preventDefault();

        const slots = cells.filter(isOn).map((cell) => ({ weekday: Number(cell.dataset.weekday), time: cell.dataset.time }));
        saveButton.classList.add('is-loading');
        saveLabel.textContent = 'Guardando…';

        try {
            const response = await http.put(grid.dataset.url, { slots });
            saved = snapshot();
            refresh();
            updateReviewBanner(response.needs_review ?? []);
            toast(response.message, 'success');
        } catch (error) {
            toast(error.message, 'error');
        } finally {
            saveButton.classList.remove('is-loading');
            saveLabel.textContent = 'Guardar cambios';
        }
    });

    window.addEventListener('beforeunload', (event) => {
        if (snapshot() !== saved) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    refresh();
}

export function initWeekGrids(root = document) {
    root.querySelectorAll('[data-week-grid]').forEach(initGrid);
}
