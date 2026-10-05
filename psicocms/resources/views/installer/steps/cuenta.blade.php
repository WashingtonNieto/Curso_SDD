@extends('installer.layout')

@section('title', 'Cuenta de acceso')
@section('heading', 'Tu cuenta privada de acceso')
@section('lead', 'Con tu email, tu teléfono y tu contraseña entrarás en tu panel de gestión. Son datos privados: no se mostrarán en tu web.')

@section('content')
    <form method="POST" action="{{ route('installer.account') }}" class="installer-form" data-loading-form novalidate>
        @csrf

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-user" aria-hidden="true"></i> Tus datos</h2>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <x-panel.form.input name="first_name" label="Nombre" :value="$user?->first_name" required autocomplete="given-name" />
                    <x-panel.form.input name="last_name" label="Apellidos" :value="$user?->last_name" required autocomplete="family-name" />
                    <x-panel.form.input name="email" label="Email de acceso" type="email" :value="$user?->email" required autocomplete="email" icon="fa-regular fa-envelope" />
                    <x-panel.form.input name="phone" label="Teléfono de acceso" type="tel" :value="$user?->phone" required autocomplete="tel" icon="fa-solid fa-phone" hint="Puedes escribirlo con espacios; lo guardaremos todo junto." />
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-lock" aria-hidden="true"></i> Contraseña</h2>
                <p class="card__subtitle">
                    @if ($user)
                        Ya guardaste una contraseña. Déjala en blanco si no quieres cambiarla.
                    @else
                        Mínimo 8 caracteres, con al menos una letra y un número.
                    @endif
                </p>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <x-panel.form.input name="password" label="Contraseña" type="password" :required="! $user" autocomplete="new-password" icon="fa-solid fa-key" />
                    <x-panel.form.input name="password_confirmation" label="Repite la contraseña" type="password" :required="! $user" autocomplete="new-password" icon="fa-solid fa-key" />
                </div>
            </div>
        </section>

        @include('installer.partials.actions')
    </form>
@endsection
