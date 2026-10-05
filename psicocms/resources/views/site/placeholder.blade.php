<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ setting('site.public_name', 'PsicoCMS') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/shared/css/coming-soon.css') }}">
</head>
<body>
    <main class="coming-soon">
        <i class="coming-soon__icon fa-solid fa-spa" aria-hidden="true"></i>
        <h1 class="coming-soon__title">{{ setting('site.public_name', 'PsicoCMS') }}</h1>
        @if (setting('site.slogan'))
            <p class="coming-soon__slogan">{{ setting('site.slogan') }}</p>
        @endif
        <p class="coming-soon__text">Estamos preparando nuestra web. Muy pronto podrás conocernos y pedir cita aquí.</p>
    </main>
</body>
</html>
