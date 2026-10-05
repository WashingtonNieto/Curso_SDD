@extends('panel.layout')

@section('title', 'Disponibilidad')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/availability.css') }}">
@endpush

@push('modules')
    <script type="module" src="{{ asset('panel/js/pages/availability.js') }}"></script>
@endpush

@php
    $modalityLabels = ['online' => 'Online', 'presencial' => 'Presencial'];
@endphp

@section('content')
    <x-panel.page-header title="Configuración de disponibilidad" subtitle="Define tus horarios de consulta. Estos huecos determinarán cuándo pueden reservar tus pacientes." />

    <div class="alert alert--warning availability__review" role="status" data-review-banner @if ($needsReview === []) hidden @endif>
        <i class="alert__icon fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        <div>
            <strong class="alert__title">Revisa y vuelve a marcar tus huecos semanales (online y presencial)</strong>
            Has cambiado la duración, el descanso o el horario. Pendiente de revisar:
            <strong data-review-list>{{ collect($needsReview)->map(fn ($modality) => $modalityLabels[$modality])->join(' y ') }}</strong>.
        </div>
    </div>

    <div class="availability__top">
        <x-panel.card class="vacation-mode {{ $vacationMode ? 'is-active' : '' }}" data-vacation-card>
            <div class="vacation-mode__row">
                <span class="vacation-mode__icon"><i class="fa-solid fa-plane-departure" aria-hidden="true"></i></span>
                <div class="vacation-mode__text">
                    <h2 class="card__title">Modo vacaciones</h2>
                    <p class="text-muted">Pausa todas las nuevas reservas. Las citas existentes no se cancelan.</p>
                </div>
            </div>
            <div class="vacation-mode__switch">
                <x-panel.toggle-switch
                    name="vacation_mode"
                    label="Activar modo vacaciones"
                    :checked="$vacationMode"
                    :off-value="null"
                    data-vacation-toggle
                    :data-url="route('panel.availability.vacation-mode')" />
                <span class="vacation-mode__status" data-vacation-status>{{ $vacationMode ? 'Reservas pausadas' : 'Reservas abiertas' }}</span>
            </div>
        </x-panel.card>

        <x-panel.card title="Periodos de vacaciones" icon="fa-solid fa-umbrella-beach" subtitle="Bloquea días concretos para que nadie pueda reservar en esas fechas.">
            <form method="POST" action="{{ route('panel.availability.vacations.store') }}" class="vacation-form" novalidate>
                @csrf
                <x-panel.form.input name="start_date" type="date" label="Desde" required />
                <x-panel.form.input name="end_date" type="date" label="Hasta" required />
                <x-panel.form.input name="note" label="Nota" optional maxlength="120" placeholder="Ej.: Verano" wrapper-class="vacation-form__note" />
                <button type="submit" class="btn btn--primary vacation-form__submit">
                    <i class="btn__icon fa-solid fa-plus" aria-hidden="true"></i>
                    <span class="btn__label">Añadir</span>
                </button>
            </form>

            @if ($vacationPeriods->isEmpty())
                <x-panel.empty-state compact icon="fa-regular fa-calendar-xmark" title="Sin vacaciones programadas" text="Cuando añadas un periodo aparecerá aquí." />
            @else
                <ul class="vacation-list">
                    @foreach ($vacationPeriods as $period)
                        <li class="vacation-list__item">
                            <span class="vacation-list__icon"><i class="fa-regular fa-calendar" aria-hidden="true"></i></span>
                            <span class="vacation-list__text">
                                <strong>
                                    @if ($period->start_date->equalTo($period->end_date))
                                        {{ $period->start_date->translatedFormat('j \d\e F \d\e Y') }}
                                    @else
                                        Del {{ $period->start_date->translatedFormat('j M Y') }} al {{ $period->end_date->translatedFormat('j M Y') }}
                                    @endif
                                </strong>
                                <span>
                                    {{ $period->note ?: trans_choice('{1} 1 día|[2,*] :count días', (int) $period->start_date->diffInDays($period->end_date) + 1) }}
                                    @if ($period->appointments_count > 0)
                                        · <span class="vacation-list__warning"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> {{ trans_choice('{1} 1 cita en estas fechas|[2,*] :count citas en estas fechas', $period->appointments_count) }}</span>
                                    @endif
                                </span>
                            </span>
                            <x-panel.confirm-delete
                                :action="route('panel.availability.vacations.destroy', $period)"
                                title="¿Eliminar este periodo de vacaciones?"
                                message="Esos días volverán a estar disponibles para reservar según tu horario semanal."
                                label="Eliminar periodo"
                                icon-only />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel.card>
    </div>

    <x-panel.tabs id="availability-tabs" :tabs="['online' => ['label' => 'Online', 'icon' => 'fa-solid fa-video'], 'presencial' => ['label' => 'Presencial', 'icon' => 'fa-solid fa-location-dot']]" :active="$activeTab">
        @foreach ($modalities as $modality => $data)
            <x-panel.tab-panel tabs="availability-tabs" :name="$modality" :active="$activeTab === $modality">
                <div class="availability__panel">
                    @include('panel.availability._schedule-form', ['modality' => $modality, 'setting' => $data['setting'], 'label' => $modalityLabels[$modality]])
                    @include('panel.availability._week-grid', ['modality' => $modality, 'grid' => $data['grid'], 'marked' => $data['marked'], 'setting' => $data['setting'], 'label' => $modalityLabels[$modality]])
                </div>
            </x-panel.tab-panel>
        @endforeach
    </x-panel.tabs>
@endsection
