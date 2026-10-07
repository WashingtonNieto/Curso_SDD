import { el } from '../core/dom.js';
import { http } from '../core/http.js';
import { spinner } from '../core/loader.js';
import { initPatientAutocomplete } from '../modules/patient-autocomplete.js';

function initAppointmentForm(form) {
    const slotsBox = form.querySelector('[data-slots]');
    const slotsWrapper = form.querySelector('[data-slots-wrapper]');
    const dateInput = form.querySelector('[data-date]');
    const customToggle = form.querySelector('[data-custom-time-toggle]');
    const customField = form.querySelector('[data-custom-time-field]');
    const priceInput = form.querySelector('[data-price]');
    const prices = JSON.parse(form.dataset.prices || '{}');

    const initial = {
        modality: form.querySelector('[data-modality]:checked')?.value ?? null,
        date: dateInput.value,
        time: slotsBox.dataset.selected || null,
    };

    let selectedTime = initial.time;
    let requestId = 0;

    const modality = () => form.querySelector('[data-modality]:checked')?.value ?? null;

    const message = (text) => slotsBox.replaceChildren(el('p', { class: 'slot-picker__message' }, text));

    const renderSlots = (times) => {
        const list = el('div', { class: 'pill-options slot-picker__list' });

        times.forEach((time) => {
            const id = `slot-${time.replace(':', '')}`;
            const input = el('input', {
                class: 'pill-option__input',
                type: 'radio',
                name: 'slot_time',
                id,
                value: time,
                checked: time === selectedTime,
            });

            input.addEventListener('change', () => {
                selectedTime = time;
            });

            list.append(el('span', { class: 'pill-option' }, input, el('label', { class: 'pill-option__label', for: id }, time)));
        });

        slotsBox.replaceChildren(list);
    };

    const loadSlots = async () => {
        const currentModality = modality();

        if (!dateInput.value || !currentModality) {
            message('Elige la modalidad y la fecha para ver tus huecos libres.');
            return;
        }

        const currentRequest = ++requestId;
        slotsBox.replaceChildren(el('p', { class: 'slot-picker__message' }, spinner('Buscando huecos libres…'), ' Buscando huecos libres…'));

        const params = new URLSearchParams({ modalidad: currentModality, fecha: dateInput.value });

        if (form.dataset.ignore) {
            params.set('ignorar', form.dataset.ignore);
        }

        try {
            const response = await http.get(`${form.dataset.slotsUrl}?${params}`);

            if (currentRequest !== requestId) {
                return;
            }

            const times = [...response.slots];
            const isOriginalDay = Boolean(form.dataset.ignore) && initial.time && currentModality === initial.modality && dateInput.value === initial.date;

            if (isOriginalDay && !times.includes(initial.time)) {
                times.push(initial.time);
                times.sort();
            }

            if (times.length === 0) {
                message(response.message || 'No quedan huecos libres ese día. Prueba otra fecha o usa una hora personalizada.');
                return;
            }

            renderSlots(times);

            if (response.message && !isOriginalDay) {
                slotsBox.append(el('p', { class: 'slot-picker__message' }, response.message));
            }
        } catch (error) {
            if (currentRequest === requestId) {
                message(error.message);
            }
        }
    };

    const syncCustomTime = () => {
        const custom = customToggle.checked;
        slotsWrapper.hidden = custom;
        customField.hidden = !custom;
    };

    const syncPrice = (previousModality) => {
        if (!priceInput) {
            return;
        }

        const previousDefault = previousModality ? String(prices[previousModality] ?? '') : '';
        const current = priceInput.value;

        if (current === '' || Number(current) === Number(previousDefault)) {
            const next = prices[modality()];
            priceInput.value = next !== null && next !== undefined ? String(Math.round(Number(next))) : '';
        }
    };

    let lastModality = modality();

    form.querySelectorAll('[data-modality]').forEach((radio) => {
        radio.addEventListener('change', () => {
            syncPrice(lastModality);
            lastModality = modality();
            loadSlots();
        });
    });

    dateInput.addEventListener('change', loadSlots);
    customToggle.addEventListener('change', syncCustomTime);

    syncCustomTime();
    syncPrice(null);
    loadSlots();
}

document.querySelectorAll('[data-appointment-form]').forEach(initAppointmentForm);
initPatientAutocomplete();
