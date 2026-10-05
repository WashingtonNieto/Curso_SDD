function toMinutes(time) {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
}

export function formatTime(minutes) {
    return `${String(Math.floor(minutes / 60)).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`;
}

export function slotTimes(duration, pause, start, end) {
    const times = [];

    if (!duration || duration <= 0 || !start || !end) {
        return times;
    }

    const last = toMinutes(end);

    for (let cursor = toMinutes(start); cursor + duration <= last; cursor += duration + pause) {
        times.push(cursor);
    }

    return times;
}

export function readSchedule(card) {
    const duration = card.querySelector('[data-schedule-duration]');
    const breakToggle = card.querySelector('[data-schedule-break-toggle]');
    const breakMinutes = card.querySelector('[data-schedule-break-minutes]');
    const start = card.querySelector('[data-schedule-start]');
    const end = card.querySelector('[data-schedule-end]');

    return {
        duration: Number(duration.value),
        breakEnabled: breakToggle.checked,
        breakMinutes: Number(breakMinutes.value || 0),
        start: start.value,
        end: end.value,
    };
}

export function initScheduleCards(root = document) {
    root.querySelectorAll('[data-schedule]').forEach((card) => {
        const duration = card.querySelector('[data-schedule-duration]');
        const breakToggle = card.querySelector('[data-schedule-break-toggle]');
        const breakMinutes = card.querySelector('[data-schedule-break-minutes]');
        const start = card.querySelector('[data-schedule-start]');
        const end = card.querySelector('[data-schedule-end]');
        const summary = card.querySelector('[data-schedule-summary]');

        const update = () => {
            const schedule = readSchedule(card);
            const pause = schedule.breakEnabled ? schedule.breakMinutes : 0;
            const times = slotTimes(schedule.duration, pause, schedule.start, schedule.end);

            breakMinutes.readOnly = !breakToggle.checked;
            card.querySelectorAll('[data-duration-preset]').forEach((preset) => {
                preset.setAttribute('aria-pressed', String(Number(preset.dataset.durationPreset) === schedule.duration));
            });

            if (times.length === 0) {
                summary.textContent = 'Con este horario no cabe ninguna sesión. Revisa la duración o las horas.';
                return;
            }

            const detail = pause > 0 ? ` (${schedule.duration} de sesión + ${pause} de descanso)` : '';
            summary.textContent = `Huecos de ${schedule.duration + pause} min${detail} · ${times.length} huecos al día: de ${formatTime(times[0])} a ${formatTime(times[times.length - 1])}`;
        };

        card.querySelectorAll('[data-duration-preset]').forEach((preset) => {
            preset.addEventListener('click', (event) => {
                event.preventDefault();
                duration.value = preset.dataset.durationPreset;
                duration.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        [duration, breakToggle, breakMinutes, start, end].forEach((field) => {
            field.addEventListener('input', update);
            field.addEventListener('change', update);
        });

        update();
    });
}
