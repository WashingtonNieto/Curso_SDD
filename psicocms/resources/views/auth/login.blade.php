<!DOCTYPE html>
<html lang="es" data-mode="light" data-accent="azul">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Acceso al panel · PsicoCMS</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/pages/login.css') }}">
</head>
<body>
    <main class="login">
        <aside class="login__visual" aria-hidden="true">
            <img class="login__image" src="{{ asset('panel/img/acceso.jpg') }}" alt="" width="1920" height="1280">
            <div class="login__visual-overlay">
                <p class="login__quote">“Cuidar de los demás empieza por tener todo en orden.”</p>
                <p class="login__quote-sub">Tus citas, pacientes e historias, en un único lugar seguro.</p>
            </div>
        </aside>

        <section class="login__panel">
            <div class="login__card">
                <div class="brand login__brand">
                    <span class="brand__logo"><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                    <span>
                        <span class="brand__name">PsicoCMS</span>
                        <span class="brand__tagline">Panel de gestión</span>
                    </span>
                </div>

                <header class="login__header">
                    <h1 class="login__title">Accede a tu panel</h1>
                    <p class="login__subtitle">Introduce tu email, tu teléfono y tu contraseña de acceso.</p>
                </header>

                @error('login')
                    <div class="alert alert--danger" role="alert">
                        <i class="alert__icon fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        <div>{{ $message }}</div>
                    </div>
                @enderror

                <form method="POST" action="{{ route('login.store') }}" class="login__form" data-loading-form novalidate>
                    @csrf

                    <x-panel.form.input name="email" label="Email" type="email" required autofocus autocomplete="username" icon="fa-regular fa-envelope" placeholder="tu@email.com" />
                    <x-panel.form.input name="phone" label="Teléfono" type="tel" required autocomplete="tel" icon="fa-solid fa-phone" placeholder="600 00 00 00" />
                    <x-panel.form.input name="password" label="Contraseña" type="password" required autocomplete="current-password" icon="fa-solid fa-key" />

                    <x-panel.toggle-switch name="remember" label="Mantener la sesión iniciada" :off-value="null" />

                    <button type="submit" class="btn btn--primary btn--lg btn--block" data-loading-text="Entrando…">
                        <span class="btn__label">Entrar</span>
                        <i class="btn__icon fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="login__security">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    Conexión protegida. Por seguridad, tras varios intentos fallidos el acceso se bloquea durante un minuto.
                </p>

                <a class="login__back" href="{{ url('/') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    Volver a la web
                </a>
            </div>
        </section>
    </main>

    <x-panel.flash />

    <script type="module" src="{{ asset('panel/js/pages/login.js') }}"></script>
</body>
</html>
