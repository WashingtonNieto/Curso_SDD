@props(['title' => null, 'subtitle' => null, 'icon' => null, 'flush' => false])

<section {{ $attributes->class('card') }}>
    @if ($title || isset($actions))
        <header class="card__header card__header--row">
            <div>
                @if ($title)
                    <h2 class="card__title">
                        @if ($icon)
                            <i class="card__title-icon {{ $icon }}" aria-hidden="true"></i>
                        @endif
                        {{ $title }}
                    </h2>
                @endif
                @if ($subtitle)
                    <p class="card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="card__actions">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div @class(['card__body', 'card__body--flush' => $flush])>
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="card__footer">{{ $footer }}</footer>
    @endisset
</section>
