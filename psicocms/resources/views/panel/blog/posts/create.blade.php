@extends('panel.layout')

@section('title', 'Nuevo artículo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/blog.css') }}">
@endpush

@push('modules')
    <script type="module" src="{{ asset('panel/js/pages/blog-post-form.js') }}"></script>
@endpush

@section('content')
    <x-panel.page-header title="Nuevo artículo" subtitle="Escribe, añade una imagen y elige si lo publicas ya o lo guardas como borrador.">
        <x-slot:actions>
            <x-panel.button variant="secondary" :href="route('panel.blog.posts.index')" icon="fa-solid fa-arrow-left">Volver al blog</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    @include('panel.blog.posts._form', [
        'action' => route('panel.blog.posts.store'),
        'submitLabel' => 'Guardar artículo',
    ])
@endsection
