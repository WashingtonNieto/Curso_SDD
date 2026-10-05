@extends('installer.layout')

@section('title', 'Base de datos')
@section('heading', 'Conectamos tu base de datos')
@section('lead', 'Indica los datos de conexión de tu servidor MySQL. Si la base de datos no existe, la crearemos automáticamente con todas sus tablas.')

@section('content')
    <form method="POST" action="{{ route('installer.database') }}" class="installer-form" data-loading-form novalidate>
        @csrf

        <div class="alert alert--info">
            <i class="alert__icon fa-solid fa-lightbulb" aria-hidden="true"></i>
            <div>
                <strong class="alert__title">¿Usas XAMPP en tu ordenador?</strong>
                Normalmente los datos son: servidor <strong>127.0.0.1</strong>, puerto <strong>3306</strong>, usuario <strong>root</strong> y contraseña vacía. Asegúrate de que MySQL está arrancado en el panel de XAMPP.
            </div>
        </div>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-server" aria-hidden="true"></i> Datos de conexión</h2>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <x-panel.form.input name="db_host" label="Servidor" :value="$defaults['db_host']" required autocomplete="off" icon="fa-solid fa-network-wired" />
                    <x-panel.form.input name="db_port" label="Puerto" type="number" :value="$defaults['db_port']" required min="1" max="65535" icon="fa-solid fa-plug" />
                    <x-panel.form.input name="db_database" label="Nombre de la base de datos" :value="$defaults['db_database']" required autocomplete="off" icon="fa-solid fa-database" hint="Solo letras sin tildes, números y guiones bajos. Ejemplo: psicocms" />
                    <x-panel.form.input name="db_username" label="Usuario" :value="$defaults['db_username']" required autocomplete="off" icon="fa-solid fa-user" />
                    <x-panel.form.input name="db_password" label="Contraseña" type="password" optional autocomplete="new-password" icon="fa-solid fa-key" wrapper-class="form-group--full" hint="Déjala vacía si tu usuario de MySQL no tiene contraseña." />
                </div>
            </div>
        </section>

        @include('installer.partials.actions', [
            'submitLabel' => 'Crear base de datos',
            'loadingText' => 'Creando la base de datos…',
            'submitIcon' => 'fa-wand-magic-sparkles',
        ])
    </form>
@endsection
