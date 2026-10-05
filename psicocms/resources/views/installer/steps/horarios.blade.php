@extends('installer.layout')

@section('title', 'Horarios')
@section('heading', 'Tus horarios y disponibilidad')
@section('lead', 'Configura por separado tus sesiones online y presenciales. Con estos datos prepararemos los huecos que tus pacientes podrán reservar.')

@section('content')
    <form method="POST" action="{{ route('installer.schedule') }}" class="installer-form" data-loading-form novalidate>
        @csrf

        <div class="schedule-grid">
            @foreach (['online' => ['Online', 'fa-solid fa-laptop'], 'presencial' => ['Presencial', 'fa-solid fa-couch']] as $modality => [$modalityLabel, $modalityIcon])
                @php
                    $values = $schedule[$modality];
                    $selectedDays = array_map('intval', old($modality.'.weekdays', old('_token') ? [] : $values['weekdays']));
                @endphp
                <section class="card schedule-card" data-schedule>
                    <header class="schedule-card__head">
                        <span class="badge badge--{{ $modality }}"><i class="{{ $modalityIcon }}" aria-hidden="true"></i> {{ $modalityLabel }}</span>
                    </header>
                    <div class="card__body">
                        <div class="form-group">
                            <label class="form-label" for="field-{{ $modality }}-session-duration">Duración de cada sesión</label>
                            <div class="duration-field">
                                <div class="input-group">
                                    <input class="form-control input-group__suffix-target @error($modality.'.session_duration') is-invalid @enderror" type="number" min="15" max="240" step="5"
                                        id="field-{{ $modality }}-session-duration" name="{{ $modality }}[session_duration]"
                                        value="{{ old($modality.'.session_duration', $values['session_duration']) }}" data-schedule-duration>
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
                                <x-panel.toggle-switch :name="$modality.'[break_enabled]'" label="Descanso entre sesiones" :checked="$values['break_enabled']" data-schedule-break-toggle />
                                <div class="input-group">
                                    <label class="sr-only" for="field-{{ $modality }}-break-minutes">Minutos de descanso</label>
                                    <input class="form-control input-group__suffix-target @error($modality.'.break_minutes') is-invalid @enderror" type="number" min="5" max="60" step="5"
                                        id="field-{{ $modality }}-break-minutes" name="{{ $modality }}[break_minutes]"
                                        value="{{ old($modality.'.break_minutes', $values['break_minutes']) }}" data-schedule-break-minutes>
                                    <span class="input-group__suffix">min</span>
                                </div>
                            </div>
                            <p class="form-hint">El descanso se suma a cada sesión: con 50 min y 10 de descanso, las citas empezarán cada hora.</p>
                            @error($modality.'.break_minutes')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-grid">
                            <x-panel.form.time-select :name="$modality.'[day_start]'" label="Hora de entrada" :value="$values['day_start']" data-schedule-start />
                            <x-panel.form.time-select :name="$modality.'[day_end]'" label="Hora de salida (máxima)" :value="$values['day_end']" data-schedule-end />
                        </div>

                        <fieldset class="form-group">
                            <legend class="form-label">Días de consulta</legend>
                            <div class="pill-options">
                                @foreach ($weekdayNames as $number => $dayName)
                                    <span class="pill-option">
                                        <input class="pill-option__input" type="checkbox" id="{{ $modality }}-day-{{ $number }}" name="{{ $modality }}[weekdays][]" value="{{ $number }}" @checked(in_array($number, $selectedDays, true))>
                                        <label class="pill-option__label" for="{{ $modality }}-day-{{ $number }}">{{ $dayName }}</label>
                                    </span>
                                @endforeach
                            </div>
                            <p class="form-hint">Si no ofreces esta modalidad, deja todos los días sin marcar.</p>
                        </fieldset>

                        <p class="slot-summary" aria-live="polite">
                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                            <span data-schedule-summary></span>
                        </p>
                    </div>
                </section>
            @endforeach
        </div>

        <div class="alert alert--info">
            <i class="alert__icon fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>Marcaremos como disponibles todos los huecos de los días elegidos. Desde tu panel podrás afinar hora a hora, añadir vacaciones o activar el modo vacaciones.</div>
        </div>

        @include('installer.partials.actions')
    </form>
@endsection
