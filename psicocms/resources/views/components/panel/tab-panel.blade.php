@props(['tabs', 'name', 'active' => false])

<div {{ $attributes->class('tabs__panel') }} role="tabpanel" id="{{ $tabs }}-panel-{{ $name }}"
    aria-labelledby="{{ $tabs }}-tab-{{ $name }}" data-tab-panel="{{ $name }}" tabindex="0" @unless ($active) hidden @endunless>
    {{ $slot }}
</div>
