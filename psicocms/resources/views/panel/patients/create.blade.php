@extends('panel.layout')

@section('title', 'Nuevo paciente')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/patients.css') }}">
@endpush

@section('content')
    <x-panel.page-header title="Nuevo paciente" subtitle="Añade a alguien con quien hayas hablado por teléfono, email, WhatsApp o en persona.">
        <x-slot:actions>
            <x-panel.button variant="secondary" :href="route('panel.patients.index')" icon="fa-solid fa-arrow-left">Volver a pacientes</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    @include('panel.patients._form', [
        'action' => route('panel.patients.store'),
        'submitLabel' => 'Guardar paciente',
    ])
@endsection
