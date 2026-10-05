@props(['action', 'resetUrl' => null, 'submitLabel' => 'Filtrar'])

<form method="GET" action="{{ $action }}" {{ $attributes->class('filters-bar') }} role="search">
    <div class="filters-bar__fields">
        {{ $slot }}
    </div>
    <div class="filters-bar__actions">
        <button type="submit" class="btn btn--primary btn--sm">
            <i class="btn__icon fa-solid fa-filter" aria-hidden="true"></i>
            <span class="btn__label">{{ $submitLabel }}</span>
        </button>
        @if ($resetUrl && request()->query())
            <a class="btn btn--ghost btn--sm" href="{{ $resetUrl }}">
                <i class="btn__icon fa-solid fa-rotate-left" aria-hidden="true"></i>
                <span class="btn__label">Limpiar</span>
            </a>
        @endif
    </div>
</form>
