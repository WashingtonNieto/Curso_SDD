@extends('panel.layout')

@section('title', 'Editar categoría')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/blog.css') }}">
@endpush

@section('content')
    <x-panel.page-header title="Editar categoría" :subtitle="$category->name">
        <x-slot:actions>
            <x-panel.button variant="secondary" :href="route('panel.blog.categories.index')" icon="fa-solid fa-arrow-left">Volver a categorías</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    <div class="category-edit">
        <x-panel.card title="Datos de la categoría" icon="fa-solid fa-tags" :subtitle="$category->posts_count > 0 ? trans_choice('{1} Tiene 1 artículo.|[2,*] Tiene :count artículos.', $category->posts_count) : 'Todavía no tiene artículos.'">
            @include('panel.blog.categories._form', [
                'action' => route('panel.blog.categories.update', $category),
                'submitLabel' => 'Guardar cambios',
            ])
        </x-panel.card>
    </div>
@endsection
