@extends('panel.layout')

@section('title', 'Editar cita')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/appointments.css') }}">
@endpush

@push('modules')
    <script type="module" src="{{ asset('panel/js/pages/appointment-form.js') }}"></script>
@endpush

@section('content')
    <x-panel.page-header title="Editar cita" :subtitle="($appointment->patient?->full_name ?? 'Paciente').' · '.$appointment->starts_at->translatedFormat('l j \d\e F \a \l\a\s H:i')">
        <x-slot:actions>
            <x-panel.button variant="secondary" :href="route('panel.appointments.index')" icon="fa-solid fa-arrow-left">Volver a citas</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    @include('panel.appointments._form', [
        'action' => route('panel.appointments.update', $appointment),
        'submitLabel' => 'Guardar cambios',
    ])

    <div class="appointment-danger">
        <x-panel.confirm-delete
            :action="route('panel.appointments.destroy', $appointment)"
            title="¿Eliminar esta cita?"
            message="La cita se eliminará definitivamente. Si solo quieres anularla, cambia su estado a “Cancelada”."
            label="Eliminar cita" />
    </div>
@endsection
