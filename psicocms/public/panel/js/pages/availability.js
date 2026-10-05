import { http } from '../core/http.js';
import { toast } from '../core/toast.js';
import { confirmAction } from '../core/confirm.js';
import { initScheduleCards, readSchedule } from '../modules/schedule-summary.js';
import { initWeekGrids } from '../modules/week-grid.js';

function initVacationMode() {
    const toggle = document.querySelector('[data-vacation-toggle]');

    if (!toggle) {
        return;
    }

    const card = document.querySelector('[data-vacation-card]');
    const status = document.querySelector('[data-vacation-status]');

    const render = (enabled) => {
        card?.classList.toggle('is-active', enabled);
        status.textContent = enabled ? 'Reservas pausadas' : 'Reservas abiertas';
    };

    toggle.addEventListener('change', async () => {
        const enabled = toggle.checked;
        toggle.disabled = true;

        try {
            const response = await http.patch(toggle.dataset.url, { enabled });
            render(response.enabled);
            toast(response.message, 'success');
        } catch (error) {
            toggle.checked = !enabled;
            render(!enabled);
            toast(error.message, 'error');
        } finally {
            toggle.disabled = false;
        }
    });
}

function initScheduleWarnings() {
    document.querySelectorAll('[data-schedule-form]').forEach((form) => {
        const initial = JSON.stringify(readSchedule(form));

        form.addEventListener('submit', async (event) => {
            if (form.dataset.confirmed === '1' || JSON.stringify(readSchedule(form)) === initial) {
                return;
            }

            event.preventDefault();

            const accepted = await confirmAction({
                title: 'Vas a cambiar tu horario',
                message: 'Al cambiar la duración, el descanso o las horas, los huecos que ya no encajen se desmarcarán. Después tendrás que revisar y volver a marcar tus huecos semanales online y presenciales.',
                confirmLabel: 'Guardar y revisar huecos',
            });

            if (accepted) {
                form.dataset.confirmed = '1';
                form.requestSubmit();
                delete form.dataset.confirmed;
            }
        });
    });
}

initScheduleCards();
initVacationMode();
initScheduleWarnings();
initWeekGrids();
