@extends('panel.layout')

@section('title', $section['title'])

@section('content')
    <x-panel.page-header :title="$section['title']" />

    <x-panel.card>
        <x-panel.empty-state
            :icon="$section['icon']"
            title="Próximamente"
            :text="$section['text']">
            @if ($query !== '')
                <p class="text-muted">Has buscado: <strong>{{ $query }}</strong></p>
            @endif
            <x-panel.button variant="secondary" :href="route('panel.home')" icon="fa-solid fa-house">Volver al inicio</x-panel.button>
        </x-panel.empty-state>
    </x-panel.card>
@endsection
