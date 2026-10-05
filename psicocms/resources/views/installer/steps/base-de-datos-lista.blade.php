@extends('installer.layout')

@section('title', 'Base de datos lista')
@section('heading', 'Base de datos preparada')

@section('content')
    <section class="card">
        <div class="success-state">
            <span class="success-state__icon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
            <h2>¡Todo listo!</h2>
            <p class="success-state__text">
                La base de datos <strong>{{ $databaseName }}</strong> se ha creado correctamente con todas sus tablas y los datos iniciales.
                Ahora vamos a crear tu cuenta privada de acceso.
            </p>
            <a class="btn btn--primary btn--lg" href="{{ route('installer.show', 'cuenta') }}">
                Continuar
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </section>
@endsection
