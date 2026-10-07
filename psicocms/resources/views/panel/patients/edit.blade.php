@extends('panel.layout')

@section('title', 'Editar paciente')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/patients.css') }}">
@endpush

@section('content')
    <x-panel.page-header title="Editar paciente" :subtitle="$patient->full_name">
        <x-slot:actions>
            <x-panel.button variant="secondary" :href="route('panel.patients.show', $patient)" icon="fa-solid fa-arrow-left">Volver a la ficha</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    @include('panel.patients._form', [
        'action' => route('panel.patients.update', $patient),
        'submitLabel' => 'Guardar cambios',
    ])
@endsection
