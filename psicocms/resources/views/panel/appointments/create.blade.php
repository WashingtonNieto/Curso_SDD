@extends('panel.layout')

@section('title', 'Nueva cita')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/appointments.css') }}">
@endpush

@push('modules')
    <script type="module" src="{{ asset('panel/js/pages/appointment-form.js') }}"></script>
@endpush

@section('content')
    <x-panel.page-header title="Nueva cita" subtitle="Añade una cita que hayas acordado por teléfono, email, WhatsApp o en persona.">
        <x-slot:actions>
            <x-panel.button variant="secondary" :href="route('panel.appointments.index')" icon="fa-solid fa-arrow-left">Volver a citas</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    @include('panel.appointments._form', [
        'action' => route('panel.appointments.store'),
        'submitLabel' => 'Crear cita',
    ])
@endsection
