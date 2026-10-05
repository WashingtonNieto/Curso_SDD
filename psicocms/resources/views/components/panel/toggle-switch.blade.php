@props([
    'name',
    'label',
    'checked' => false,
    'value' => '1',
    'offValue' => '0',
    'id' => null,
    'errorKey' => null,
])

@php
    $errorKey ??= trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= 'toggle-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $errorKey));
    $isChecked = (bool) old($errorKey, $checked);
@endphp

<label class="toggle" for="{{ $id }}">
    @if (! is_null($offValue))
        <input type="hidden" name="{{ $name }}" value="{{ $offValue }}">
    @endif
    <input {{ $attributes->class('toggle__input') }} type="checkbox" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" role="switch" @checked($isChecked)>
    <span class="toggle__track" aria-hidden="true"></span>
    <span class="toggle__label">{{ $label }}</span>
</label>
