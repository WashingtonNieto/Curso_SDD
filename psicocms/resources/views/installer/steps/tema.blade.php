@extends('installer.layout')

@section('title', 'Tema visual')
@section('heading', 'Elige el diseño de tu web')
@section('lead', 'Selecciona el tema que más te guste. Podrás cambiarlo en cualquier momento sin perder ningún dato.')

@php
    $selectedTheme = old('theme', $activeTheme);
    $selectedMode = old('theme_mode', $activeMode);
    $demoChecked = (bool) old('demo_content', $demoContent);
@endphp

@section('content')
    <form method="POST" action="{{ route('installer.theme') }}" class="installer-form" data-loading-form novalidate>
        @csrf

        <fieldset class="card">
            <div class="card__header">
                <legend class="card__title installer-section__title"><i class="fa-solid fa-palette" aria-hidden="true"></i> Tema visual</legend>
            </div>
            <div class="card__body">
                <div class="theme-grid">
                    @foreach ($themes as $slug => $theme)
                        @php $preview = $theme['preview']; @endphp
                        <label class="theme-card" style="--tp-primary: {{ $preview['primary'] ?? '#3d5f8a' }}; --tp-secondary: {{ $preview['secondary'] ?? '#c9d6e8' }}; --tp-background: {{ $preview['background'] ?? '#ffffff' }}; --tp-text: {{ $preview['text'] ?? '#1e2533' }}; --tp-font: '{{ $preview['font'] ?? 'Lexend' }}'; --tp-heading-font: '{{ $preview['heading_font'] ?? 'Lexend' }}';">
                            <input class="theme-card__input" type="radio" name="theme" value="{{ $slug }}" @checked($selectedTheme === $slug)>
                            <span class="theme-card__body">
                                <span class="theme-card__check"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                <span class="theme-preview theme-preview--{{ $preview['layout'] ?? 'split' }}" aria-hidden="true">
                                    <span class="theme-preview__nav">
                                        <span class="theme-preview__logo"></span>
                                        <span class="theme-preview__links"><span></span><span></span><span></span></span>
                                    </span>
                                    <span class="theme-preview__hero">
                                        <span class="theme-preview__copy">
                                            <span class="theme-preview__title">Tu bienestar empieza hoy</span>
                                            <span class="theme-preview__line"></span>
                                            <span class="theme-preview__line theme-preview__line--short"></span>
                                            <span class="theme-preview__button"></span>
                                        </span>
                                        <span class="theme-preview__media"></span>
                                    </span>
                                </span>
                                <span class="theme-card__info">
                                    <span class="theme-card__name">{{ $theme['name'] }}</span>
                                    <span class="theme-card__description">{{ $theme['description'] ?? '' }}</span>
                                    <span class="theme-card__swatches" aria-hidden="true">
                                        <span class="theme-card__swatch theme-card__swatch--primary"></span>
                                        <span class="theme-card__swatch theme-card__swatch--secondary"></span>
                                        <span class="theme-card__swatch theme-card__swatch--background"></span>
                                    </span>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('theme')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </fieldset>

        <fieldset class="card">
            <div class="card__header">
                <legend class="card__title installer-section__title"><i class="fa-solid fa-table-columns" aria-hidden="true"></i> Formato de la web</legend>
            </div>
            <div class="card__body">
                <div class="mode-options">
                    <label class="mode-option">
                        <input class="mode-option__input" type="radio" name="theme_mode" value="landing" @checked($selectedMode === 'landing')>
                        <span class="mode-option__body">
                            <i class="fa-solid fa-scroll" aria-hidden="true"></i>
                            <span>
                                <span class="mode-option__title">Landing (una sola página)</span>
                                <span class="mode-option__text">Toda la información en una página larga con desplazamiento suave. Ideal para empezar.</span>
                            </span>
                        </span>
                    </label>
                    <label class="mode-option">
                        <input class="mode-option__input" type="radio" name="theme_mode" value="multipage" @checked($selectedMode === 'multipage')>
                        <span class="mode-option__body">
                            <i class="fa-regular fa-copy" aria-hidden="true"></i>
                            <span>
                                <span class="mode-option__title">Multipágina</span>
                                <span class="mode-option__text">Secciones separadas: inicio, sobre mí, servicios, blog, pide cita…</span>
                            </span>
                        </span>
                    </label>
                </div>
                @error('theme_mode')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </fieldset>

        <section class="card">
            <div class="card__body">
                <input type="hidden" name="demo_content" value="0">
                <label class="check" for="demo-content">
                    <input class="check__input" type="checkbox" id="demo-content" name="demo_content" value="1" @checked($demoChecked)>
                    <span>
                        <strong>Cargar contenido de ejemplo</strong><br>
                        <span class="text-muted">Añade pacientes, citas, artículos del blog y preguntas frecuentes ficticios para que puedas explorar el panel. Podrás borrarlos cuando quieras.</span>
                    </span>
                </label>
            </div>
        </section>

        @include('installer.partials.actions')
    </form>
@endsection
