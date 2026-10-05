@props(['variant' => 'primary', 'size' => null, 'icon' => null, 'href' => null, 'type' => 'button', 'block' => false])

@php
    $classes = ['btn', 'btn--'.$variant, 'btn--'.$size => $size, 'btn--block' => $block];
@endphp

@if ($href)
    <a {{ $attributes->class($classes) }} href="{{ $href }}">
        @if ($icon)
            <i class="btn__icon {{ $icon }}" aria-hidden="true"></i>
        @endif
        <span class="btn__label">{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->class($classes) }} type="{{ $type }}">
        @if ($icon)
            <i class="btn__icon {{ $icon }}" aria-hidden="true"></i>
        @endif
        <span class="btn__label">{{ $slot }}</span>
    </button>
@endif
