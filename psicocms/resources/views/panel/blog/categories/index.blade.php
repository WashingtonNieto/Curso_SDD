@extends('panel.layout')

@section('title', 'Categorías del blog')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/blog.css') }}">
@endpush

@section('content')
    <x-panel.page-header title="Categorías del blog" subtitle="Organiza tus artículos por temas para que tus pacientes encuentren fácilmente lo que buscan.">
        <x-slot:actions>
            <x-panel.button variant="secondary" :href="route('panel.blog.posts.index')" icon="fa-regular fa-newspaper">Ver artículos</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    <div class="form-layout">
        <x-panel.card flush title="Tus categorías" icon="fa-solid fa-tags" :subtitle="trans_choice('{0} Ninguna categoría|{1} 1 categoría|[2,*] :count categorías', $categories->count())">
            @if ($categories->isEmpty())
                <x-panel.empty-state icon="fa-solid fa-tags" title="Aún no tienes categorías" text="Crea tu primera categoría con el formulario de la derecha." />
            @else
                <x-panel.table caption="Listado de categorías del blog">
                    <x-slot:head>
                        <th scope="col">Nombre</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Artículos</th>
                        <th scope="col"><span class="sr-only">Acciones</span></th>
                    </x-slot:head>

                    @foreach ($categories as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                @if ($item->description)
                                    <span class="table__muted">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</span>
                                @endif
                            </td>
                            <td><code class="category-slug">{{ $item->slug }}</code></td>
                            <td>
                                @if ($item->posts_count > 0)
                                    <a href="{{ route('panel.blog.posts.index', ['categoria' => $item->id]) }}">{{ trans_choice('{1} 1 artículo|[2,*] :count artículos', $item->posts_count) }}</a>
                                @else
                                    <span class="table__muted">Sin artículos</span>
                                @endif
                            </td>
                            <td>
                                <div class="table__actions">
                                    <a class="btn btn--icon btn--ghost" href="{{ route('panel.blog.categories.edit', $item) }}" aria-label="Editar la categoría {{ $item->name }}" title="Editar">
                                        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                    </a>
                                    <x-panel.confirm-delete
                                        :action="route('panel.blog.categories.destroy', $item)"
                                        title="¿Eliminar esta categoría?"
                                        :message="$item->posts_count > 0
                                            ? 'Se eliminará «'.$item->name.'». '.trans_choice('{1} Su artículo no se borrará: quedará sin categoría.|[2,*] Sus :count artículos no se borrarán: quedarán sin categoría.', $item->posts_count)
                                            : 'Se eliminará «'.$item->name.'». No tiene artículos asociados.'"
                                        label="Eliminar categoría"
                                        icon-only />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-panel.table>
            @endif
        </x-panel.card>

        <x-panel.card title="Nueva categoría" icon="fa-solid fa-plus">
            @include('panel.blog.categories._form', [
                'action' => route('panel.blog.categories.store'),
                'submitLabel' => 'Crear categoría',
            ])
        </x-panel.card>
    </div>
@endsection
