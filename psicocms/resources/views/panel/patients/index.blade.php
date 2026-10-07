@extends('panel.layout')

@section('title', 'Pacientes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/patients.css') }}">
@endpush

@push('modules')
    <script type="module" src="{{ asset('panel/js/pages/patients.js') }}"></script>
@endpush

@use('App\Models\Appointment')
@use('App\Models\Patient')

@section('content')
    <x-panel.page-header title="Gestión de pacientes" subtitle="Consulta y administra las fichas de tus pacientes.">
        <x-slot:actions>
            <x-panel.button :href="route('panel.patients.create')" icon="fa-solid fa-user-plus">Nuevo paciente</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    <div class="stats-grid">
        <x-panel.stat-card label="Total pacientes" :value="$stats['total']" icon="fa-solid fa-user-group" tone="primary" data-stat="total" />
        <x-panel.stat-card label="Activos este mes" :value="$stats['activeThisMonth']" icon="fa-solid fa-chart-line" tone="success" hint="Con alguna cita este mes" data-stat="activeThisMonth" />
        <x-panel.stat-card label="Consultas hoy" :value="$stats['today']" icon="fa-regular fa-clock" tone="info" :href="route('panel.calendar')" data-stat="today" />
    </div>

    <x-panel.card flush>
        @if (! $hasAny)
            <x-panel.empty-state icon="fa-solid fa-user-group" title="Aún no tienes pacientes" text="Se crearán solos cuando alguien reserve desde tu web o cuando añadas una cita. También puedes añadirlos tú.">
                <x-panel.button :href="route('panel.patients.create')" icon="fa-solid fa-user-plus">Añadir el primer paciente</x-panel.button>
            </x-panel.empty-state>
        @else
            <div data-patients
                data-list-url="{{ route('panel.patients.list') }}"
                data-create-url="{{ route('panel.patients.create') }}">
                <x-panel.filters-bar :action="route('panel.patients.index')" :reset-url="route('panel.patients.index')" data-patients-filters>
                    <x-panel.form.input name="q" label="Buscar" :value="$filters['q'] ?? ''" icon="fa-solid fa-magnifying-glass" placeholder="Nombre, teléfono o email" autocomplete="off" />
                    <x-panel.form.select name="estado" label="Estado" :options="Patient::STATUSES" :value="$filters['estado'] ?? ''" placeholder="Todos" />
                    <x-panel.form.select name="modalidad" label="Modalidad preferida" :options="Appointment::MODALITIES" :value="$filters['modalidad'] ?? ''" placeholder="Todas" />
                    <x-panel.form.select name="genero" label="Género" :options="Patient::GENDERS" :value="$filters['genero'] ?? ''" placeholder="Todos" />
                </x-panel.filters-bar>

                <div class="table-wrap" data-patients-table>
                    <table class="table patients-table">
                        <caption class="sr-only">Listado de pacientes</caption>
                        <thead>
                            <tr>
                                <th scope="col">Paciente</th>
                                <th scope="col">Última cita</th>
                                <th scope="col">Próxima cita</th>
                                <th scope="col">Modalidad</th>
                                <th scope="col">Estado</th>
                                <th scope="col"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody data-patients-body></tbody>
                    </table>
                </div>

                <div data-patients-empty hidden></div>
                <div data-patients-pagination aria-live="polite"></div>
            </div>
        @endif
    </x-panel.card>
@endsection
