@props(['title', 'subtitle' => null])

<header {{ $attributes->class('page-header') }}>
    <div class="page-header__text">
        <h1 class="page-header__title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="page-header__actions">{{ $actions }}</div>
    @endisset
</header>
