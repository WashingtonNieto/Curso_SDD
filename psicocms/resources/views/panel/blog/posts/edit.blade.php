@extends('panel.layout')

@section('title', 'Editar artículo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/blog.css') }}">
@endpush

@push('modules')
    <script type="module" src="{{ asset('panel/js/pages/blog-post-form.js') }}"></script>
@endpush

@section('content')
    <x-panel.page-header title="Editar artículo" :subtitle="$post->title">
        <x-slot:actions>
            <x-panel.badge type="post" :value="$post->status" />
            <x-panel.confirm-delete
                :action="route('panel.blog.posts.destroy', $post)"
                title="¿Eliminar este artículo?"
                :message="'Se eliminará «'.$post->title.'» junto con su imagen destacada. Si solo quieres ocultarlo de tu web, cámbialo a “Borrador”.'"
                label="Eliminar" />
            <x-panel.button variant="secondary" :href="route('panel.blog.posts.index')" icon="fa-solid fa-arrow-left">Volver al blog</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    @include('panel.blog.posts._form', [
        'action' => route('panel.blog.posts.update', $post),
        'submitLabel' => 'Guardar cambios',
    ])
@endsection
