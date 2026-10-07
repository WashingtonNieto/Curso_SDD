@extends('panel.layout')

@section('title', 'Artículos del blog')

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/blog.css') }}">
@endpush

@use('App\Models\BlogPost')

@php
    $isFiltered = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
@endphp

@section('content')
    <x-panel.page-header title="Gestión del blog" subtitle="Administra tus publicaciones y contenido educativo.">
        <x-slot:actions>
            <x-panel.button :href="route('panel.blog.posts.create')" icon="fa-solid fa-file-pen">Crear nuevo artículo</x-panel.button>
        </x-slot:actions>
    </x-panel.page-header>

    <div class="stats-grid">
        <x-panel.stat-card label="Total artículos" :value="$stats['total']" icon="fa-regular fa-newspaper" tone="info" :href="route('panel.blog.posts.index')" />
        <x-panel.stat-card label="Publicados" :value="$stats['published']" icon="fa-regular fa-circle-check" tone="primary" :href="route('panel.blog.posts.index', ['estado' => 'publicado'])" />
        <x-panel.stat-card label="Borradores" :value="$stats['drafts']" icon="fa-regular fa-file-lines" tone="success" :href="route('panel.blog.posts.index', ['estado' => 'borrador'])" />
    </div>

    <x-panel.card flush>
        @if ($stats['total'] > 0)
            <x-panel.filters-bar :action="route('panel.blog.posts.index')" :reset-url="route('panel.blog.posts.index')">
                <x-panel.form.input name="q" label="Buscar" :value="$filters['q'] ?? ''" icon="fa-solid fa-magnifying-glass" placeholder="Buscar artículos…" />
                <x-panel.form.select name="categoria" label="Categoría" :options="$categoryOptions" :value="$filters['categoria'] ?? ''" placeholder="Todas" />
                <x-panel.form.select name="estado" label="Estado" :options="BlogPost::STATUSES" :value="$filters['estado'] ?? ''" placeholder="Todos" />
            </x-panel.filters-bar>
        @endif

        @if ($posts->isEmpty())
            @if ($isFiltered)
                <x-panel.empty-state icon="fa-solid fa-filter-circle-xmark" title="No hay artículos con estos filtros" text="Prueba con otras palabras o quita algún filtro.">
                    <x-panel.button variant="secondary" :href="route('panel.blog.posts.index')" icon="fa-solid fa-rotate-left">Quitar filtros</x-panel.button>
                </x-panel.empty-state>
            @else
                <x-panel.empty-state icon="fa-regular fa-newspaper" title="Aún no has escrito ningún artículo" text="Compartir lo que sabes ayuda a que tus pacientes te conozcan y te encuentren en Google.">
                    <x-panel.button :href="route('panel.blog.posts.create')" icon="fa-solid fa-file-pen">Escribir mi primer artículo</x-panel.button>
                </x-panel.empty-state>
            @endif
        @else
            <x-panel.table caption="Listado de artículos del blog">
                <x-slot:head>
                    <th scope="col">Título del artículo</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="sr-only">Acciones</span></th>
                </x-slot:head>

                @foreach ($posts as $post)
                    @php
                        $summary = $post->excerpt ?: \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags((string) $post->content))), 90);
                        $isScheduled = $post->status === 'publicado' && $post->published_at?->isFuture();
                    @endphp
                    <tr>
                        <td>
                            <div class="post-row">
                                <span class="thumb">
                                    @if ($post->image_path)
                                        <img src="{{ public_storage_url($post->image_path) }}" alt="" loading="lazy">
                                    @else
                                        <i class="fa-regular fa-image" aria-hidden="true"></i>
                                    @endif
                                </span>
                                <span class="post-row__text">
                                    <a class="post-row__title" href="{{ route('panel.blog.posts.edit', $post) }}">{{ $post->title }}</a>
                                    @if ($summary)
                                        <span class="post-row__excerpt">{{ \Illuminate\Support\Str::limit($summary, 90) }}</span>
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td>
                            @if ($post->category)
                                <a class="post-row__category" href="{{ route('panel.blog.posts.index', ['categoria' => $post->category->id]) }}">{{ $post->category->name }}</a>
                            @else
                                <span class="table__muted">Sin categoría</span>
                            @endif
                        </td>
                        <td class="post-row__date">
                            @if ($post->published_at)
                                {{ $post->published_at->translatedFormat('j M Y') }}
                                @if ($isScheduled)
                                    <span class="table__muted"><i class="fa-regular fa-clock" aria-hidden="true"></i> Programado</span>
                                @endif
                            @else
                                <span class="table__muted">—</span>
                            @endif
                        </td>
                        <td><x-panel.badge type="post" :value="$post->status" /></td>
                        <td>
                            <div class="table__actions">
                                <a class="btn btn--icon btn--ghost" href="{{ route('panel.blog.posts.edit', $post) }}" aria-label="Editar «{{ $post->title }}»" title="Editar">
                                    <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                                </a>
                                <x-panel.confirm-delete
                                    :action="route('panel.blog.posts.destroy', $post)"
                                    title="¿Eliminar este artículo?"
                                    :message="'Se eliminará «'.$post->title.'» junto con su imagen destacada. Si solo quieres ocultarlo de tu web, cámbialo a “Borrador”.'"
                                    label="Eliminar artículo"
                                    icon-only />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            {{ $posts->links('vendor.pagination.panel') }}
        @endif
    </x-panel.card>
@endsection
