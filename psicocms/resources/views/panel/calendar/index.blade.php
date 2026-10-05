@extends('panel.layout')

@section('title', 'Calendario')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/calendar.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/fullcalendar/index.global.min.js') }}"></script>
    <script src="{{ asset('vendor/fullcalendar/locales/es.global.min.js') }}"></script>
@endpush

@push('modules')
    <script type="module" src="{{ asset('panel/js/pages/calendar.js') }}"></script>
@endpush

@section('content')
    <h1 class="sr-only">Calendario de citas</h1>

    @if ($vacationMode)
        <div class="alert alert--warning calendar-alert" role="status">
            <i class="alert__icon fa-solid fa-plane-departure" aria-hidden="true"></i>
            <div>Tienes activado el <strong>modo vacaciones</strong>: tus pacientes no pueden reservar nuevas citas. <a href="{{ route('panel.availability') }}">Gestionar disponibilidad</a></div>
        </div>
    @endif

    <div class="calendar-page" data-calendar-page data-config='@json($config)'>
        <div class="calendar-page__main">
            <div class="calendar-toolbar">
                <h2 class="calendar-toolbar__title" data-cal-title aria-live="polite">Calendario</h2>

                <div class="calendar-toolbar__nav" role="group" aria-label="Navegar por el calendario">
                    <button type="button" class="calendar-toolbar__arrow" data-cal-prev aria-label="Anterior">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="calendar-toolbar__today" data-cal-today>Hoy</button>
                    <button type="button" class="calendar-toolbar__arrow" data-cal-next aria-label="Siguiente">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="calendar-toolbar__views" role="group" aria-label="Tipo de vista">
                    <button type="button" class="calendar-toolbar__view" data-cal-view="dayGridMonth" aria-pressed="false">Mes</button>
                    <button type="button" class="calendar-toolbar__view" data-cal-view="timeGridWeek" aria-pressed="false">Semana</button>
                    <button type="button" class="calendar-toolbar__view" data-cal-view="timeGridDay" aria-pressed="false">Día</button>
                    <button type="button" class="calendar-toolbar__view" data-cal-view="listWeek" aria-pressed="false">Lista</button>
                </div>
            </div>

            <div class="card calendar-card">
                <div class="calendar-card__loading" data-cal-loading hidden>
                    <span class="spinner" role="status" aria-label="Cargando citas…"></span>
                </div>
                <div class="calendar" data-calendar></div>
            </div>

            <ul class="calendar-legend" aria-label="Leyenda">
                <li><span class="calendar-legend__swatch calendar-legend__swatch--online"></span> Online</li>
                <li><span class="calendar-legend__swatch calendar-legend__swatch--presencial"></span> Presencial</li>
                <li><span class="calendar-legend__swatch calendar-legend__swatch--cancelled"></span> Cancelada</li>
                <li><span class="calendar-legend__swatch calendar-legend__swatch--vacation"></span> Vacaciones</li>
                <li><span class="calendar-legend__swatch calendar-legend__swatch--business"></span> Tu horario</li>
            </ul>
            <p class="calendar-help">
                <i class="fa-regular fa-lightbulb" aria-hidden="true"></i>
                Arrastra una cita para cambiarla de día u hora. En las vistas de semana y día, pulsa en un hueco vacío para crear una cita a esa hora.
            </p>
        </div>

        <aside class="calendar-page__side">
            <x-panel.button :href="route('panel.appointments.create')" icon="fa-solid fa-circle-plus" size="lg" block class="calendar-page__new" data-cal-new>
                Nueva cita
            </x-panel.button>

            <section class="card agenda" aria-labelledby="agenda-title">
                <header class="agenda__header">
                    <h2 class="agenda__title" id="agenda-title">Agenda del día</h2>
                    <p class="agenda__date">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <span data-agenda-date></span>
                    </p>
                    <a class="agenda__add" href="{{ route('panel.appointments.create') }}" data-agenda-add>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> Añadir cita este día
                    </a>
                </header>
                <div class="agenda__body" data-agenda-list aria-live="polite"></div>
            </section>
        </aside>
    </div>

    <x-panel.modal id="appointment-modal" title="Detalle de la cita" icon="fa-regular fa-calendar-check">
        <div class="appointment-detail">
            <div class="appointment-detail__head">
                <span class="avatar avatar--lg" data-detail="initials" aria-hidden="true"></span>
                <div>
                    <p class="appointment-detail__name" data-detail="title"></p>
                    <p class="appointment-detail__badges">
                        <span class="badge" data-detail-badge="modality"></span>
                        <span class="badge" data-detail-badge="status"></span>
                    </p>
                </div>
            </div>

            <dl class="appointment-detail__list">
                <div>
                    <dt><i class="fa-regular fa-calendar" aria-hidden="true"></i> Fecha</dt>
                    <dd data-detail="dateLabel"></dd>
                </div>
                <div>
                    <dt><i class="fa-regular fa-clock" aria-hidden="true"></i> Hora</dt>
                    <dd data-detail="timeLabel"></dd>
                </div>
                <div>
                    <dt><i class="fa-solid fa-phone" aria-hidden="true"></i> Teléfono</dt>
                    <dd><a data-detail-link="phone"></a></dd>
                </div>
                <div data-detail-row="email">
                    <dt><i class="fa-regular fa-envelope" aria-hidden="true"></i> Email</dt>
                    <dd><a data-detail-link="email"></a></dd>
                </div>
                <div data-detail-row="price">
                    <dt><i class="fa-solid fa-dollar-sign" aria-hidden="true"></i> Precio</dt>
                    <dd data-detail="price"></dd>
                </div>
                <div>
                    <dt><i class="fa-solid fa-route" aria-hidden="true"></i> Origen</dt>
                    <dd data-detail="source"></dd>
                </div>
                <div class="appointment-detail__full" data-detail-row="reason">
                    <dt><i class="fa-regular fa-comment" aria-hidden="true"></i> Motivo</dt>
                    <dd data-detail="reason"></dd>
                </div>
            </dl>

            <form class="appointment-detail__status" data-detail-status-form>
                <label class="form-label" for="detail-status">Cambiar estado</label>
                <div class="appointment-detail__status-row">
                    <select class="form-control" id="detail-status" name="status" data-detail-status>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn--secondary">Guardar</button>
                </div>
            </form>
        </div>

        <x-slot:footer>
            <a class="btn btn--ghost" data-detail-url="patient" href="#">
                <i class="btn__icon fa-regular fa-user" aria-hidden="true"></i>
                <span class="btn__label">Ver paciente</span>
            </a>
            <button type="button" class="btn btn--soft-danger" data-detail-delete>
                <i class="btn__icon fa-regular fa-trash-can" aria-hidden="true"></i>
                <span class="btn__label">Eliminar</span>
            </button>
            <a class="btn btn--primary" data-detail-url="edit" href="#">
                <i class="btn__icon fa-regular fa-pen-to-square" aria-hidden="true"></i>
                <span class="btn__label">Editar</span>
            </a>
        </x-slot:footer>
    </x-panel.modal>
@endsection
