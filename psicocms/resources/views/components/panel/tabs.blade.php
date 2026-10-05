@props(['id', 'tabs' => [], 'active' => null])

@php($active ??= array_key_first($tabs))

<div {{ $attributes->class('tabs') }} id="{{ $id }}" data-tabs>
    <div class="tabs__list" role="tablist">
        @foreach ($tabs as $key => $tab)
            @php
                $tabLabel = is_array($tab) ? $tab['label'] : $tab;
                $tabIcon = is_array($tab) ? ($tab['icon'] ?? null) : null;
                $selected = (string) $key === (string) $active;
            @endphp
            <button type="button" class="tabs__tab" role="tab" id="{{ $id }}-tab-{{ $key }}"
                aria-controls="{{ $id }}-panel-{{ $key }}" aria-selected="{{ $selected ? 'true' : 'false' }}"
                tabindex="{{ $selected ? '0' : '-1' }}" data-tab="{{ $key }}">
                @if ($tabIcon)
                    <i class="{{ $tabIcon }}" aria-hidden="true"></i>
                @endif
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    {{ $slot }}
</div>
