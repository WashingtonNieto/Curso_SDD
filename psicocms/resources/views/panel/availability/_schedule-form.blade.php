<x-panel.card :title="'Duración y descanso · '.$label" icon="fa-regular fa-hourglass-half" subtitle="La duración y el descanso determinan cada cuánto empieza una cita.">
    <form method="POST" action="{{ route('panel.availability.schedule', $modality) }}" class="schedule-form" data-schedule data-schedule-form novalidate>
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label" for="field-{{ $modality }}-session-duration">Duración de cada sesión</label>
            <div class="duration-field">
                <div class="input-group">
                    <input class="form-control input-group__suffix-target @error($modality.'.session_duration') is-invalid @enderror" type="number" min="15" max="240" step="5"
                        id="field-{{ $modality }}-session-duration" name="{{ $modality }}[session_duration]"
                        value="{{ old($modality.'.session_duration', $setting->session_duration) }}" data-schedule-duration>
                    <span class="input-group__suffix">min</span>
                </div>
                @foreach ($presets as $preset)
                    <button type="button" class="btn btn--secondary btn--sm" data-duration-preset="{{ $preset }}" aria-pressed="false">{{ $preset }} min</button>
                @endforeach
            </div>
            @error($modality.'.session_duration')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <div class="break-row">
                <x-panel.toggle-switch :name="$modality.'[break_enabled]'" label="Descanso entre sesiones" :checked="$setting->break_enabled" data-schedule-break-toggle />
                <div class="input-group">
                    <label class="sr-only" for="field-{{ $modality }}-break-minutes">Minutos de descanso</label>
                    <input class="form-control input-group__suffix-target @error($modality.'.break_minutes') is-invalid @enderror" type="number" min="5" max="60" step="5"
                        id="field-{{ $modality }}-break-minutes" name="{{ $modality }}[break_minutes]"
                        value="{{ old($modality.'.break_minutes', $setting->break_minutes) }}" data-schedule-break-minutes>
                    <span class="input-group__suffix">min</span>
                </div>
            </div>
            <p class="form-hint">El descanso se suma a cada sesión: con 50 min y 10 de descanso, las citas empiezan cada hora.</p>
            @error($modality.'.break_minutes')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-grid">
            <x-panel.form.time-select :name="$modality.'[day_start]'" label="Hora de entrada" :value="substr($setting->day_start, 0, 5)" data-schedule-start />
            <x-panel.form.time-select :name="$modality.'[day_end]'" label="Hora de salida (máxima)" :value="substr($setting->day_end, 0, 5)" data-schedule-end />
        </div>

        <p class="slot-summary" aria-live="polite">
            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
            <span data-schedule-summary></span>
        </p>

        <div class="schedule-form__actions">
            <button type="submit" class="btn btn--primary">
                <i class="btn__icon fa-regular fa-floppy-disk" aria-hidden="true"></i>
                <span class="btn__label">Guardar horario</span>
            </button>
        </div>
    </form>
</x-panel.card>
