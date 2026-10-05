@extends('installer.layout')

@section('title', 'Finalizar')
@section('heading', '¡Ya casi está!')
@section('lead', 'Revisa el resumen de tu configuración. Si quieres cambiar algo, pulsa en “Editar”.')

@section('content')
    <div class="installer-form">
        <div class="summary-grid">
            <section class="card summary-item">
                <span class="summary-item__icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                <div>
                    <h2 class="summary-item__title">Cuenta de acceso</h2>
                    <p class="summary-item__text">
                        {{ $user?->full_name }}<br>
                        {{ $user?->email }} · {{ $user?->phone }}
                    </p>
                    <a class="summary-item__edit" href="{{ route('installer.show', 'cuenta') }}">Editar</a>
                </div>
            </section>

            <section class="card summary-item">
                <span class="summary-item__icon"><i class="fa-solid fa-id-card" aria-hidden="true"></i></span>
                <div>
                    <h2 class="summary-item__title">Datos de tu web</h2>
                    <p class="summary-item__text">
                        {{ $publicName }}<br>
                        {{ trans_choice('{0} Sin especialidades|{1} 1 especialidad|[2,*] :count especialidades', $specialtiesCount) }}
                        · {{ trans_choice('{0} sin servicios|{1} 1 servicio|[2,*] :count servicios', $servicesCount) }}
                        @if ($plans->isNotEmpty())
                            <br>
                            @foreach ($plans as $plan)
                                {{ $plan->name }}: {{ money($plan->price) }}@if (! $loop->last) · @endif
                            @endforeach
                        @endif
                    </p>
                    <a class="summary-item__edit" href="{{ route('installer.show', 'datos-publicos') }}">Editar</a>
                </div>
            </section>

            <section class="card summary-item">
                <span class="summary-item__icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
                <div>
                    <h2 class="summary-item__title">Horarios</h2>
                    <p class="summary-item__text">
                        @foreach ($scheduleSummary as $modality => $item)
                            <strong>{{ ucfirst($modality) }}:</strong>
                            @if ($item['days'] === 0)
                                no disponible
                            @else
                                {{ $item['duration'] }} min{{ $item['break'] ? ' + '.$item['break'].' de descanso' : '' }}, de {{ $item['from'] }} a {{ $item['to'] }}, {{ trans_choice('{1} 1 día|[2,*] :count días', $item['days']) }} a la semana
                            @endif
                            @if (! $loop->last)<br>@endif
                        @endforeach
                    </p>
                    <a class="summary-item__edit" href="{{ route('installer.show', 'horarios') }}">Editar</a>
                </div>
            </section>

            <section class="card summary-item">
                @if ($photoUrl)
                    <img class="summary-photo" src="{{ $photoUrl }}" alt="Tu foto">
                @else
                    <span class="summary-item__icon"><i class="fa-regular fa-image" aria-hidden="true"></i></span>
                @endif
                <div>
                    <h2 class="summary-item__title">Foto</h2>
                    <p class="summary-item__text">{{ $photoUrl ? 'Foto subida correctamente.' : 'Sin foto por ahora.' }}</p>
                    <a class="summary-item__edit" href="{{ route('installer.show', 'foto') }}">Editar</a>
                </div>
            </section>

            <section class="card summary-item">
                <span class="summary-item__icon"><i class="fa-solid fa-palette" aria-hidden="true"></i></span>
                <div>
                    <h2 class="summary-item__title">Tema visual</h2>
                    <p class="summary-item__text">
                        {{ $theme['name'] ?? 'Sin elegir' }} · {{ $themeMode }}<br>
                        {{ $demoContent ? 'Se cargará contenido de ejemplo.' : 'Sin contenido de ejemplo.' }}
                    </p>
                    <a class="summary-item__edit" href="{{ route('installer.show', 'tema') }}">Editar</a>
                </div>
            </section>
        </div>

        <form method="POST" action="{{ route('installer.finish') }}" data-loading-form>
            @csrf
            @include('installer.partials.actions', [
                'submitLabel' => 'Finalizar e ir a mi panel',
                'loadingText' => 'Preparando tu panel…',
                'submitIcon' => 'fa-flag-checkered',
            ])
        </form>
    </div>
@endsection
