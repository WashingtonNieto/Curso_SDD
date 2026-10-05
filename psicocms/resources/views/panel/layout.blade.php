@php($panelUser = auth()->user())
<!DOCTYPE html>
<html lang="es" data-mode="{{ $panelUser->panel_mode ?: 'light' }}" data-accent="{{ $panelUser->panel_color ?: 'azul' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Panel') · PsicoCMS</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/components.css') }}">
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <div class="panel">
        @include('panel.partials.sidebar')
        <div class="panel__overlay" data-sidebar-overlay hidden></div>

        <div class="panel__main">
            @include('panel.partials.topbar', ['user' => $panelUser])

            <main class="panel__content" id="contenido" tabindex="-1">
                @yield('content')
            </main>
        </div>
    </div>

    <x-panel.flash />

    @stack('scripts')
    <script type="module" src="{{ asset('panel/js/pages/panel.js') }}"></script>
    @stack('modules')
</body>
</html>
