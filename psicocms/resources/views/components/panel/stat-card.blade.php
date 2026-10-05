@props(['label', 'value', 'icon', 'tone' => 'primary', 'hint' => null, 'href' => null])

@php($tag = $href ? 'a' : 'div')

<{{ $tag }} {{ $attributes->class(['card', 'stat-card']) }} @if ($href) href="{{ $href }}" @endif>
    <span class="stat-card__icon stat-card__icon--{{ $tone }}">
        <i class="{{ $icon }}" aria-hidden="true"></i>
    </span>
    <span class="stat-card__text">
        <span class="stat-card__label">{{ $label }}</span>
        <span class="stat-card__value">{{ $value }}</span>
        @if ($hint)
            <span class="stat-card__hint">{{ $hint }}</span>
        @endif
    </span>
</{{ $tag }}>
