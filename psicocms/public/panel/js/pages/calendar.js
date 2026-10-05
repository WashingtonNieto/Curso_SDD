import { el, icon } from '../core/dom.js';
import { http } from '../core/http.js';
import { toast } from '../core/toast.js';
import { openModal, closeModal } from '../core/modal.js';
import { confirmAction } from '../core/confirm.js';

const STATUS_TONES = { pendiente: 'warning', confirmada: 'primary', completada: 'success', cancelada: 'danger', no_asistio: 'neutral' };
const MODALITY_ICONS = { online: 'fa-solid fa-video', presencial: 'fa-solid fa-location-dot' };
const MOBILE_QUERY = window.matchMedia('(max-width: 768px)');

const pad = (value) => String(value).padStart(2, '0');
const toDateParam = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
const toTimeParam = (date) => `${pad(date.getHours())}:${pad(date.getMinutes())}`;
const sameDay = (a, b) => toDateParam(a) === toDateParam(b);
const capitalize = (text) => text.charAt(0).toUpperCase() + text.slice(1);

function formatGap(minutes) {
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    if (hours === 0) {
        return `${rest} min`;
    }

    return rest === 0 ? `${hours} h` : `${hours} h ${rest} min`;
}

function initCalendar(page) {
    const config = JSON.parse(page.dataset.config);
    const container = page.querySelector('[data-calendar]');
    const title = page.querySelector('[data-cal-title]');
    const loading = page.querySelector('[data-cal-loading]');
    const viewButtons = [...page.querySelectorAll('[data-cal-view]')];
    const agendaList = page.querySelector('[data-agenda-list]');
    const agendaDate = page.querySelector('[data-agenda-date]');
    const agendaAdd = page.querySelector('[data-agenda-add]');
    const modal = document.getElementById('appointment-modal');

    let selectedDate = new Date();
    let currentEvent = null;

    const createUrl = (date, withTime = false) => {
        const params = new URLSearchParams({ fecha: toDateParam(date) });

        if (withTime) {
            params.set('hora', toTimeParam(date));
        }

        return `${config.createUrl}?${params}`;
    };

    const markSelectedDay = () => {
        container.querySelectorAll('.fc-daygrid-day.is-selected').forEach((cell) => cell.classList.remove('is-selected'));
        container.querySelector(`.fc-daygrid-day[data-date="${toDateParam(selectedDate)}"]`)?.classList.add('is-selected');
    };

    const appointmentEvents = (date) => calendar.getEvents()
        .filter((event) => event.extendedProps.type === 'appointment' && event.extendedProps.status !== 'cancelada' && sameDay(event.start, date))
        .sort((a, b) => a.start - b.start);

    const agendaItem = (event) => {
        const props = event.extendedProps;
        const isPast = event.end && event.end < new Date();

        const button = el(
            'button',
            { type: 'button', class: `agenda__item agenda__item--${props.modality}${isPast ? ' is-past' : ''}` },
            el('span', { class: 'agenda__time' }, el('strong', {}, toTimeParam(event.start)), el('span', {}, `${props.duration} min`)),
            el(
                'span',
                { class: 'agenda__info' },
                el('strong', { class: 'agenda__name' }, event.title),
                el('span', { class: 'agenda__modality' }, icon(MODALITY_ICONS[props.modality]), ` ${props.modalityLabel}`),
            ),
        );

        button.addEventListener('click', (clickEvent) => {
            clickEvent.preventDefault();
            openDetail(event);
        });

        return button;
    };

    const renderAgenda = () => {
        const isToday = sameDay(selectedDate, new Date());
        const events = appointmentEvents(selectedDate);
        const nodes = [];

        agendaDate.textContent = capitalize(selectedDate.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' }));
        agendaAdd.href = createUrl(selectedDate);

        events.forEach((event, index) => {
            if (index > 0) {
                const previous = events[index - 1];
                const freeFrom = new Date(previous.end.getTime() + (previous.extendedProps.breakMinutes || 0) * 60000);
                const gap = Math.round((event.start - freeFrom) / 60000);

                if (gap >= 30) {
                    nodes.push(el('p', { class: 'agenda__separator' }, el('span', {}, `Descanso · ${formatGap(gap)}`)));
                }
            }

            nodes.push(agendaItem(event));
        });

        const remaining = events.filter((event) => event.end > new Date()).length;

        if (events.length === 0 || (isToday && remaining === 0)) {
            nodes.push(el(
                'div',
                { class: 'agenda__empty' },
                icon('fa-solid fa-mug-hot'),
                el('p', {}, isToday ? 'No hay más citas programadas para hoy.' : 'No hay citas programadas este día.'),
            ));
        }

        agendaList.replaceChildren(...nodes);
    };

    const selectDay = (date) => {
        selectedDate = date;
        markSelectedDay();
        renderAgenda();
    };

    const setDetail = (key, value) => {
        modal.querySelectorAll(`[data-detail="${key}"]`).forEach((node) => {
            node.textContent = value ?? '';
        });
    };

    const toggleRow = (key, visible) => {
        const row = modal.querySelector(`[data-detail-row="${key}"]`);

        if (row) {
            row.hidden = !visible;
        }
    };

    const openDetail = (event) => {
        const props = event.extendedProps;
        currentEvent = event;

        setDetail('title', event.title);
        setDetail('initials', props.initials);
        setDetail('dateLabel', props.dateLabel);
        setDetail('timeLabel', props.timeLabel);
        setDetail('source', props.source);
        setDetail('price', props.price);
        setDetail('reason', props.reason);

        const modalityBadge = modal.querySelector('[data-detail-badge="modality"]');
        modalityBadge.className = `badge badge--${props.modality}`;
        modalityBadge.replaceChildren(icon(`badge__icon ${MODALITY_ICONS[props.modality]}`), ` ${props.modalityLabel}`);

        const statusBadge = modal.querySelector('[data-detail-badge="status"]');
        statusBadge.className = `badge badge--${STATUS_TONES[props.status] ?? 'neutral'}`;
        statusBadge.textContent = props.statusLabel;

        const phone = modal.querySelector('[data-detail-link="phone"]');
        phone.textContent = props.phone ?? '';
        phone.href = props.phone ? `tel:${props.phone}` : '#';

        const email = modal.querySelector('[data-detail-link="email"]');
        email.textContent = props.email ?? '';
        email.href = props.email ? `mailto:${props.email}` : '#';

        toggleRow('email', Boolean(props.email));
        toggleRow('price', Boolean(props.price));
        toggleRow('reason', Boolean(props.reason));

        modal.querySelector('[data-detail-status]').value = props.status;
        modal.querySelector('[data-detail-url="edit"]').href = props.urls.edit;
        modal.querySelector('[data-detail-url="patient"]').href = props.urls.patient;

        openModal(modal);
    };

    const calendar = new window.FullCalendar.Calendar(container, {
        locale: 'es',
        firstDay: 1,
        headerToolbar: false,
        initialView: MOBILE_QUERY.matches ? 'listWeek' : 'dayGridMonth',
        height: 'auto',
        allDaySlot: false,
        nowIndicator: true,
        dayMaxEvents: 3,
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00',
        scrollTime: config.scrollTime,
        slotDuration: '00:30:00',
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        businessHours: config.businessHours.length ? config.businessHours : false,
        editable: true,
        eventDurationEditable: false,
        noEventsContent: 'No hay citas en este periodo',
        events: async (info, success, failure) => {
            try {
                const params = new URLSearchParams({ start: info.startStr.slice(0, 10), end: info.endStr.slice(0, 10) });
                success(await http.get(`${config.eventsUrl}?${params}`));
            } catch (error) {
                toast(error.message, 'error');
                failure(error);
            }
        },
        loading: (isLoading) => {
            loading.hidden = !isLoading;
        },
        datesSet: (info) => {
            title.textContent = capitalize(info.view.title);
            viewButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.calView === info.view.type)));

            if (selectedDate < info.view.currentStart || selectedDate >= info.view.currentEnd) {
                const today = new Date();
                selectedDate = today >= info.view.currentStart && today < info.view.currentEnd ? today : new Date(info.view.currentStart);
            }

            markSelectedDay();
        },
        dayCellClassNames: (arg) => (sameDay(arg.date, selectedDate) ? ['is-selected'] : []),
        eventsSet: () => {
            markSelectedDay();
            renderAgenda();
        },
        dateClick: (info) => {
            if (info.view.type.startsWith('timeGrid')) {
                window.location.href = createUrl(info.date, true);
                return;
            }

            selectDay(info.date);
        },
        eventClick: (info) => {
            info.jsEvent.preventDefault();

            if (info.event.extendedProps.type !== 'appointment') {
                return;
            }

            selectDay(info.event.start);
            openDetail(info.event);
        },
        eventDrop: async (info) => {
            const start = `${toDateParam(info.event.start)} ${toTimeParam(info.event.start)}`;

            try {
                const response = await http.patch(info.event.extendedProps.urls.move, { start });
                toast(response.message, 'success');
                calendar.refetchEvents();
            } catch (error) {
                info.revert();
                toast(error.message, 'error');
            }
        },
        eventContent: (arg) => {
            if (arg.event.display === 'background') {
                return arg.view.type === 'dayGridMonth'
                    ? { domNodes: [el('span', { class: 'fc-vacation__label' }, icon('fa-solid fa-umbrella-beach'), ` ${arg.event.title}`)] }
                    : { domNodes: [] };
            }

            const props = arg.event.extendedProps;
            const nodes = [icon(`fc-event__icon ${MODALITY_ICONS[props.modality]}`)];

            if (!arg.view.type.startsWith('list')) {
                nodes.push(el('span', { class: 'fc-event__time' }, arg.timeText));
            }

            nodes.push(el('span', { class: 'fc-event__name' }, arg.event.title));

            return { domNodes: [el('span', { class: 'fc-event__content' }, ...nodes)] };
        },
        eventDidMount: (info) => {
            if (info.event.extendedProps.type === 'appointment') {
                const props = info.event.extendedProps;
                info.el.title = `${props.timeLabel} · ${info.event.title} · ${props.modalityLabel} · ${props.statusLabel}`;
            }
        },
    });

    page.querySelector('[data-cal-prev]').addEventListener('click', (event) => {
        event.preventDefault();
        calendar.prev();
    });

    page.querySelector('[data-cal-next]').addEventListener('click', (event) => {
        event.preventDefault();
        calendar.next();
    });

    page.querySelector('[data-cal-today]').addEventListener('click', (event) => {
        event.preventDefault();
        calendar.today();
        selectDay(new Date());
    });

    viewButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            calendar.changeView(button.dataset.calView, selectedDate);
        });
    });

    modal.querySelector('[data-detail-status-form]').addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!currentEvent) {
            return;
        }

        try {
            const response = await http.patch(currentEvent.extendedProps.urls.status, { status: modal.querySelector('[data-detail-status]').value });
            toast(response.message, 'success');
            closeModal(modal);
            calendar.refetchEvents();
        } catch (error) {
            toast(error.message, 'error');
        }
    });

    modal.querySelector('[data-detail-delete]').addEventListener('click', async (event) => {
        event.preventDefault();

        if (!currentEvent) {
            return;
        }

        const target = currentEvent;
        closeModal(modal);

        const accepted = await confirmAction({
            title: '¿Eliminar esta cita?',
            message: `Se eliminará la cita de ${target.title} (${target.extendedProps.dateLabel}, ${target.extendedProps.timeLabel}). Si solo quieres anularla, cambia su estado a “Cancelada”.`,
            confirmLabel: 'Sí, eliminar',
        });

        if (!accepted) {
            return;
        }

        try {
            const response = await http.delete(target.extendedProps.urls.delete);
            toast(response.message, 'success');
            calendar.refetchEvents();
        } catch (error) {
            toast(error.message, 'error');
        }
    });

    calendar.render();
    renderAgenda();
}

const page = document.querySelector('[data-calendar-page]');

if (page && window.FullCalendar) {
    initCalendar(page);
}
