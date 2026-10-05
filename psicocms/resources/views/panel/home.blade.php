@extends('panel.layout')

@section('title', 'Inicio')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/dashboard.css') }}">
@endpush

@section('content')
    <x-panel.page-header :title="'¡Hola, '.auth()->user()->first_name.'!'" subtitle="Aquí tienes un resumen de tu actividad para hoy.">
        <x-slot:actions>
            <x-panel.button :href="route('panel.appointments.create')" icon="fa-solid fa-plus">Nueva cita</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    @if ($vacationMode)
        <div class="alert alert--warning dashboard__alert" role="status">
            <i class="alert__icon fa-solid fa-plane-departure" aria-hidden="true"></i>
            <div>
                <strong class="alert__title">Modo vacaciones activado</strong>
                Tus pacientes no pueden reservar nuevas citas. <a href="{{ route('panel.availability') }}">Gestionar disponibilidad</a>
            </div>
        </div>
    @endif

    @if ($needsReview)
        <div class="alert alert--info dashboard__alert" role="status">
            <i class="alert__icon fa-solid fa-calendar-week" aria-hidden="true"></i>
            <div>Has cambiado tu horario: <a href="{{ route('panel.availability') }}">revisa y vuelve a marcar tus huecos semanales</a>.</div>
        </div>
    @endif

    <div class="stats-grid">
        <x-panel.stat-card label="Citas de hoy" :value="$summary['appointmentsToday']" icon="fa-regular fa-calendar-check" tone="primary" :href="route('panel.appointments.index', ['desde' => today()->toDateString(), 'hasta' => today()->toDateString()])" />
        <x-panel.stat-card label="Pacientes activos" :value="$summary['activePatients']" icon="fa-solid fa-user-group" tone="success" :href="route('panel.patients.index')" />
        <x-panel.stat-card label="Artículos publicados" :value="$summary['publishedPosts']" icon="fa-regular fa-newspaper" tone="info" :href="route('panel.blog.posts.index')" />
        <x-panel.stat-card label="Ingresos del mes" :value="money($summary['monthIncome'])" icon="fa-solid fa-dollar-sign" tone="warning" hint="Citas no canceladas de este mes" />
    </div>

    <div class="dashboard">
        <div class="dashboard__main">
            <x-panel.card title="Próximas citas de hoy" icon="fa-regular fa-clock" flush>
                <x-slot:actions>
                    <a class="card__link" href="{{ route('panel.appointments.index') }}">Ver todas</a>
                </x-slot:actions>

                @if ($todayAppointments->isEmpty())
                    <x-panel.empty-state compact icon="fa-solid fa-mug-hot" title="Hoy no tienes citas" text="Disfruta del día o aprovecha para ponerte al día con tus historias clínicas.">
                        <x-panel.button variant="secondary" size="sm" :href="route('panel.appointments.create')" icon="fa-solid fa-plus">Añadir una cita</x-panel.button>
                    </x-panel.empty-state>
                @else
                    <x-panel.table caption="Citas de hoy">
                        <x-slot:head>
                            <th scope="col">Hora</th>
                            <th scope="col">Paciente</th>
                            <th scope="col">Tipo</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </x-slot:head>
                        @foreach ($todayAppointments as $appointment)
                            <tr @class(['is-past' => $appointment->ends_at->isPast()])>
                                <td>
                                    <strong>{{ $appointment->starts_at->format('H:i') }}</strong>
                                    <span class="table__muted">hasta {{ $appointment->ends_at->format('H:i') }}</span>
                                </td>
                                <td>
                                    <span class="table__person">
                                        <span class="avatar avatar--sm" aria-hidden="true">{{ $appointment->patient?->initials }}</span>
                                        <span>
                                            {{ $appointment->patient?->full_name }}
                                            <span class="table__muted">{{ $appointment->patient?->phone }}</span>
                                        </span>
                                    </span>
                                </td>
                                <td><x-panel.badge type="modality" :value="$appointment->modality" /></td>
                                <td>
                                    <div class="table__actions">
                                        <a class="btn btn--icon btn--ghost" href="{{ route('panel.appointments.edit', $appointment) }}" aria-label="Editar la cita de {{ $appointment->patient?->full_name }}" title="Editar">
                                            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-panel.table>
                @endif
            </x-panel.card>

            <x-panel.card title="Citas de las últimas 8 semanas" icon="fa-solid fa-chart-column">
                @if (collect($activity)->sum('count') === 0)
                    <x-panel.empty-state compact icon="fa-solid fa-chart-column" title="Todavía no hay actividad" text="Cuando tengas citas, aquí verás cómo evoluciona tu consulta semana a semana." />
                @else
                    <ol class="bar-chart" aria-label="Número de citas por semana">
                        @foreach ($activity as $week)
                            <li class="bar-chart__item {{ $week['current'] ? 'is-current' : '' }}" title="{{ $week['range'] }}: {{ $week['count'] }} citas">
                                <span class="bar-chart__value">{{ $week['count'] }}</span>
                                <span class="bar-chart__track">
                                    <span class="bar-chart__bar" style="--bar-height: {{ max($week['percent'], 2) }}%"></span>
                                </span>
                                <span class="bar-chart__label">{{ $week['label'] }}</span>
                                <span class="sr-only">{{ $week['range'] }}: {{ $week['count'] }} citas</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-panel.card>
        </div>

        <aside class="dashboard__side">
            <x-panel.card title="Disponibilidad semanal" icon="fa-regular fa-calendar">
                <x-slot:actions>
                    <a class="btn btn--icon btn--ghost" href="{{ route('panel.availability') }}" aria-label="Editar disponibilidad" title="Editar disponibilidad">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    </a>
                </x-slot:actions>

                <ul class="week-summary">
                    @foreach ($weeklyAvailability as $day)
                        @php
                            $dayRanges = collect($day['online'])->map(fn ($range) => ['online', $range])
                                ->concat(collect($day['presencial'])->map(fn ($range) => ['presencial', $range]));
                        @endphp
                        <li class="week-summary__day">
                            <span class="week-summary__name">{{ $day['name'] }}</span>
                            <span class="week-summary__ranges">
                                @forelse ($dayRanges as [$modality, $range])
                                    <span class="week-summary__range week-summary__range--{{ $modality }}" title="{{ ucfirst($modality) }}">
                                        <i class="fa-solid {{ $modality === 'online' ? 'fa-video' : 'fa-location-dot' }}" aria-hidden="true"></i>
                                        <span class="sr-only">{{ ucfirst($modality) }}:</span>
                                        {{ $range[0] }} – {{ $range[1] }}
                                    </span>
                                @empty
                                    <span class="week-summary__none">No disponible</span>
                                @endforelse
                            </span>
                        </li>
                    @endforeach
                </ul>
                <p class="week-summary__legend">
                    <span><i class="fa-solid fa-video" aria-hidden="true"></i> Online</span>
                    <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Presencial</span>
                </p>
            </x-panel.card>

            <x-panel.card title="Nuevas reservas web" icon="fa-solid fa-globe">
                @if ($newBookings->isEmpty())
                    <x-panel.empty-state compact icon="fa-regular fa-envelope-open" title="Sin reservas nuevas" text="Aquí aparecerán las citas que tus pacientes reserven desde tu web." />
                @else
                    <ul class="booking-list">
                        @foreach ($newBookings as $booking)
                            <li>
                                <a class="booking-list__item" href="{{ route('panel.appointments.edit', $booking) }}">
                                    <span class="avatar avatar--sm" aria-hidden="true">{{ $booking->patient?->initials }}</span>
                                    <span class="booking-list__text">
                                        <strong>{{ $booking->patient?->full_name }}</strong>
                                        <span>{{ $booking->starts_at->translatedFormat('D j M · H:i') }}</span>
                                    </span>
                                    <x-panel.badge type="modality" :value="$booking->modality" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-panel.card>
        </aside>
    </div>
@endsection
