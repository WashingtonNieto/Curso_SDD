@extends('installer.layout')

@section('title', 'Bienvenida')
@section('heading', '¡Te damos la bienvenida a PsicoCMS!')
@section('lead', 'En unos minutos tendrás lista tu web profesional y tu panel privado de gestión. Te acompañamos paso a paso.')

@php
    $allOk = collect($checks)->every(fn ($check) => $check['ok']);
@endphp

@section('content')
    <div class="installer-form">
        <section class="card">
            <div class="card__body">
                <div class="welcome-hero">
                    <span class="welcome-hero__icon"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i></span>
                    <div>
                        <h2>¿Qué vamos a preparar juntas?</h2>
                        <p class="text-muted">Solo te pediremos lo imprescindible para poner tu web en marcha.</p>
                    </div>
                </div>

                <ul class="welcome-list">
                    <li><i class="fa-solid fa-database" aria-hidden="true"></i> La base de datos, de forma automática</li>
                    <li><i class="fa-solid fa-lock" aria-hidden="true"></i> Tu cuenta privada de acceso</li>
                    <li><i class="fa-solid fa-id-card" aria-hidden="true"></i> Los datos que verán tus pacientes</li>
                    <li><i class="fa-regular fa-clock" aria-hidden="true"></i> Tus horarios online y presenciales</li>
                    <li><i class="fa-regular fa-image" aria-hidden="true"></i> Tu foto profesional</li>
                    <li><i class="fa-solid fa-palette" aria-hidden="true"></i> El diseño de tu web</li>
                </ul>
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title">Comprobación del servidor</h2>
                <p class="card__subtitle">Revisamos que todo esté preparado para instalar PsicoCMS.</p>
            </div>
            <div class="card__body">
                <ul class="requirements">
                    @foreach (collect($checks)->groupBy('group') as $group => $items)
                        <li class="requirements__group">{{ $group }}</li>
                        @foreach ($items as $check)
                            <li class="requirements__item {{ $check['ok'] ? 'is-ok' : 'is-ko' }}">
                                <span class="requirements__status">
                                    <i class="fa-solid {{ $check['ok'] ? 'fa-check' : 'fa-xmark' }}" aria-hidden="true"></i>
                                    <span class="sr-only">{{ $check['ok'] ? 'Correcto' : 'Pendiente' }}</span>
                                </span>
                                <span>
                                    <span class="requirements__label">{{ $check['label'] }}</span>
                                    <span class="requirements__detail">{{ $check['detail'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </section>

        @unless ($allOk)
            <div class="alert alert--warning" role="alert">
                <i class="alert__icon fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                <div>Corrige los puntos marcados en rojo y recarga esta página para continuar.</div>
            </div>
        @endunless

        <form method="POST" action="{{ route('installer.requirements') }}" class="installer-actions">
            @csrf
            <button type="submit" class="btn btn--primary btn--lg installer-actions__end" @disabled(! $allOk)>
                Empezar la instalación
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>
    </div>
@endsection
