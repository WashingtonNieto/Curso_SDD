<!DOCTYPE html>
<html lang="es" data-mode="light" data-accent="azul">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · Instalación de PsicoCMS</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/pages/installer.css') }}">
    @stack('styles')
</head>
<body>
    <div class="installer">
        <aside class="installer__aside" aria-label="Pasos de la instalación">
            <div class="brand">
                <span class="brand__logo"><i class="fa-solid fa-spa" aria-hidden="true"></i></span>
                <span>
                    <span class="brand__name">PsicoCMS</span>
                    <span class="brand__tagline">Asistente de instalación</span>
                </span>
            </div>

            <ol class="installer-steps">
                @foreach ($steps as $slug => $label)
                    @php
                        $isCurrent = $slug === $currentStep;
                        $isDone = in_array($slug, $completedSteps, true) && ! $isCurrent;
                    @endphp
                    <li class="installer-steps__item {{ $isDone ? 'is-done' : '' }} {{ $isCurrent ? 'is-current' : '' }}" @if ($isCurrent) aria-current="step" @endif>
                        <span class="installer-steps__marker">
                            @if ($isDone)
                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                <span class="sr-only">Completado:</span>
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        <span class="installer-steps__label">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>

            <p class="installer__aside-note">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <span>No te preocupes por dejarlo todo perfecto: podrás cambiar y ampliar estos datos cuando quieras desde tu panel.</span>
            </p>
        </aside>

        <main class="installer__main">
            <div class="installer__container">
                <div class="installer__mobile-progress">
                    <span>Paso {{ $stepNumber }} de {{ $totalSteps }} · {{ $steps[$currentStep] }}</span>
                    <progress class="installer__progress" value="{{ $stepNumber }}" max="{{ $totalSteps }}">Paso {{ $stepNumber }} de {{ $totalSteps }}</progress>
                </div>

                <header class="installer__header">
                    <span class="installer__eyebrow">Paso {{ $stepNumber }} de {{ $totalSteps }}</span>
                    <h1>@yield('heading')</h1>
                    @hasSection('lead')
                        <p class="installer__lead">@yield('lead')</p>
                    @endif
                </header>

                @if ($errors->any())
                    <div class="installer__alerts">
                        @foreach (['requirements', 'database', 'account'] as $generalError)
                            @error($generalError)
                                <div class="alert alert--danger" role="alert">
                                    <i class="alert__icon fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                    <div>{{ $message }}</div>
                                </div>
                            @enderror
                        @endforeach

                        @if ($errors->hasAny(collect($errors->keys())->diff(['requirements', 'database', 'account'])->all()))
                            <div class="alert alert--danger" role="alert">
                                <i class="alert__icon fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                <div>
                                    <strong class="alert__title">Revisa los campos marcados</strong>
                                    Hay algunos datos que necesitan tu atención antes de continuar.
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <x-panel.flash />

    @stack('scripts')
    <script type="module" src="{{ asset('panel/js/pages/installer.js') }}"></script>
</body>
</html>
