@extends('panel.layout')

@section('title', 'Citas')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/appointments.css') }}">
@endpush

@use('App\Models\Appointment')

@php
    $isFiltered = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
@endphp

@section('content')
    <x-panel.page-header title="Gestión de citas" subtitle="Consulta, filtra y gestiona todas tus citas.">
        <x-slot:actions>
            <x-panel.button :href="route('panel.appointments.create')" icon="fa-solid fa-plus">Nueva cita</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    <x-panel.card flush>
        @if ($hasAny)
            <x-panel.filters-bar :action="route('panel.appointments.index')" :reset-url="route('panel.appointments.index')">
                <x-panel.form.input name="q" label="Buscar" :value="$filters['q'] ?? ''" icon="fa-solid fa-magnifying-glass" placeholder="Paciente, teléfono o motivo" />
                <x-panel.form.input name="desde" type="date" label="Desde" :value="$filters['desde'] ?? ''" />
                <x-panel.form.input name="hasta" type="date" label="Hasta" :value="$filters['hasta'] ?? ''" />
                <x-panel.form.select name="modalidad" label="Modalidad" :options="Appointment::MODALITIES" :value="$filters['modalidad'] ?? ''" placeholder="Todas" />
                <x-panel.form.select name="estado" label="Estado" :options="Appointment::STATUSES" :value="$filters['estado'] ?? ''" placeholder="Todos" />
                <x-panel.form.select name="origen" label="Origen" :options="Appointment::SOURCES" :value="$filters['origen'] ?? ''" placeholder="Todos" />
            </x-panel.filters-bar>
        @endif

        @if ($appointments->isEmpty())
            @if ($isFiltered)
                <x-panel.empty-state icon="fa-solid fa-filter-circle-xmark" title="No hay citas con estos filtros" text="Prueba a cambiar las fechas o a quitar algún filtro.">
                    <x-panel.button variant="secondary" :href="route('panel.appointments.index')" icon="fa-solid fa-rotate-left">Quitar filtros</x-panel.button>
                </x-panel.empty-state>
            @else
                <x-panel.empty-state icon="fa-regular fa-calendar-plus" title="Aún no tienes citas" text="Cuando tus pacientes reserven desde tu web o añadas una cita a mano, aparecerán aquí.">
                    <x-panel.button :href="route('panel.appointments.create')" icon="fa-solid fa-plus">Crear la primera cita</x-panel.button>
                </x-panel.empty-state>
            @endif
        @else
            <x-panel.table caption="Listado de citas">
                <x-slot:head>
                    <th scope="col">Fecha y hora</th>
                    <th scope="col">Paciente</th>
                    <th scope="col">Modalidad</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Origen</th>
                    <th scope="col">Precio</th>
                    <th scope="col"><span class="sr-only">Acciones</span></th>
                </x-slot:head>

                @foreach ($appointments as $appointment)
                    <tr>
                        <td>
                            <strong>{{ $appointment->starts_at->translatedFormat('D j M Y') }}</strong>
                            <span class="table__muted">{{ $appointment->starts_at->format('H:i') }} – {{ $appointment->ends_at->format('H:i') }}</span>
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
                            <form method="POST" action="{{ route('panel.appointments.status', $appointment) }}" class="status-form">
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="status-{{ $appointment->id }}">Estado de la cita</label>
                                <select class="status-select status-select--{{ $appointment->status }}" id="status-{{ $appointment->id }}" name="status" data-auto-submit>
                                    @foreach (Appointment::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($appointment->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td>{{ Appointment::SOURCES[$appointment->source] ?? $appointment->source }}</td>
                        <td>{{ $appointment->price !== null ? money($appointment->price) : '—' }}</td>
                        <td>
                            <div class="table__actions">
                                <a class="btn btn--icon btn--ghost" href="{{ route('panel.appointments.edit', $appointment) }}" aria-label="Editar la cita de {{ $appointment->patient?->full_name }}" title="Editar">
                                    <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                </a>
                                <x-panel.confirm-delete
                                    :action="route('panel.appointments.destroy', $appointment)"
                                    title="¿Eliminar esta cita?"
                                    :message="'Se eliminará la cita de '.($appointment->patient?->full_name ?? 'este paciente').' del '.$appointment->starts_at->translatedFormat('j \d\e F \a \l\a\s H:i').'. Si solo quieres anularla, cambia su estado a “Cancelada”.'"
                                    label="Eliminar cita"
                                    icon-only />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            {{ $appointments->links('vendor.pagination.panel') }}
        @endif
    </x-panel.card>
@endsection
