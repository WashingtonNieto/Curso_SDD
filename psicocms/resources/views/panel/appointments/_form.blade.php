@use('App\Models\Appointment')

@php
    $patient = $appointment->patient;
    $selectedModality = old('modality', $appointment->modality);
    $customTime = (bool) old('custom_time', false);
    $selectedTime = old('slot_time', $initialTime);
@endphp

<form method="POST" action="{{ $action }}" class="appointment-form" data-appointment-form
    data-slots-url="{{ route('panel.availability.slots') }}"
    data-ignore="{{ $appointment->id }}"
    data-prices='@json($prices)'
    novalidate>
    @csrf
    @if ($appointment->exists)
        @method('PUT')
    @endif

    <div class="appointment-form__grid">
        <div class="appointment-form__main">
            <x-panel.card title="Paciente" icon="fa-regular fa-user" subtitle="Si el teléfono ya existe, la cita se vinculará a ese paciente; si no, se creará su ficha automáticamente.">
                <div class="form-grid">
                    <x-panel.form.input name="first_name" label="Nombre" :value="$patient?->first_name" required autocomplete="off" icon="fa-solid fa-magnifying-glass" :data-patient-autocomplete="route('panel.patients.search')" placeholder="Escribe para buscar un paciente" hint="Busca por nombre o teléfono y elige al paciente para rellenar sus datos." />
                    <x-panel.form.input name="last_name" label="Apellidos" :value="$patient?->last_name" optional autocomplete="off" />
                    <x-panel.form.input name="phone" label="Teléfono" type="tel" :value="$patient?->phone" required icon="fa-solid fa-phone" autocomplete="off" />
                    <x-panel.form.input name="email" label="Email" type="email" :value="$patient?->email" optional icon="fa-regular fa-envelope" autocomplete="off" />
                </div>
            </x-panel.card>

            <x-panel.card title="Fecha y hora" icon="fa-regular fa-calendar">
                <div class="appointment-form__schedule">
                    <fieldset class="form-group">
                        <legend class="form-label">Modalidad</legend>
                        <div class="pill-options">
                            @foreach (Appointment::MODALITIES as $value => $label)
                                <span class="pill-option">
                                    <input class="pill-option__input" type="radio" id="modality-{{ $value }}" name="modality" value="{{ $value }}" @checked($selectedModality === $value) data-modality>
                                    <label class="pill-option__label" for="modality-{{ $value }}">
                                        <i class="fa-solid {{ $value === 'online' ? 'fa-video' : 'fa-location-dot' }}" aria-hidden="true"></i>
                                        {{ $label }}
                                    </label>
                                </span>
                            @endforeach
                        </div>
                        @error('modality')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <x-panel.form.input name="date" type="date" label="Fecha" :value="$initialDate" required data-date wrapper-class="appointment-form__date" />

                    <fieldset class="form-group" data-slots-wrapper @if ($customTime) hidden @endif>
                        <legend class="form-label">Huecos libres</legend>
                        <div class="slot-picker" data-slots data-selected="{{ $selectedTime }}" aria-live="polite">
                            <p class="slot-picker__message">Elige una fecha para ver tus huecos libres.</p>
                        </div>
                        @error('slot_time')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div class="custom-time">
                        <x-panel.toggle-switch name="custom_time" label="Hora personalizada (fuera de mi disponibilidad)" :checked="$customTime" data-custom-time-toggle />
                        <div class="custom-time__field" data-custom-time-field @unless ($customTime) hidden @endunless>
                            <x-panel.form.input name="custom_time_value" type="time" label="Hora de inicio" :value="old('custom_time_value', $initialTime)" step="300" hint="Se seguirá comprobando que no se solape con otras citas." />
                        </div>
                    </div>
                </div>
            </x-panel.card>
        </div>

        <div class="appointment-form__side">
            <x-panel.card title="Detalles" icon="fa-solid fa-sliders">
                <div class="appointment-form__details">
                    <x-panel.form.select name="status" label="Estado" :options="Appointment::STATUSES" :value="$appointment->status" />
                    <x-panel.form.select name="source" label="¿Cómo se ha pedido la cita?" :options="Appointment::SOURCES" :value="$appointment->source" />
                    <x-panel.form.input name="price" label="Precio" type="number" step="1" min="0" :max="config('psicocms.currency.max_price')" :value="$appointment->price !== null ? (int) $appointment->price : null" :suffix="config('psicocms.currency.code')" optional data-price hint="Se rellena con el precio de tu plan para esta modalidad." />
                    <x-panel.form.textarea name="reason" label="Motivo de la consulta" :value="$appointment->reason" optional rows="3" />
                    <x-panel.form.textarea name="internal_notes" label="Notas internas" :value="$appointment->internal_notes" optional rows="3" hint="Solo las ves tú." />
                </div>
            </x-panel.card>

            <div class="appointment-form__actions">
                <x-panel.button variant="secondary" :href="route('panel.appointments.index')">Cancelar</x-panel.button>
                <button type="submit" class="btn btn--primary" data-loading-text="Guardando…">
                    <i class="btn__icon fa-regular fa-floppy-disk" aria-hidden="true"></i>
                    <span class="btn__label">{{ $submitLabel }}</span>
                </button>
            </div>
        </div>
    </div>
</form>
