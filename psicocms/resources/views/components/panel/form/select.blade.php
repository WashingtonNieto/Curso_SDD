@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'optional' => false,
    'id' => null,
    'errorKey' => null,
    'wrapperClass' => '',
])

@php
    $errorKey ??= trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= 'field-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $errorKey));
    $hasError = $errors->has($errorKey);
    $selected = (string) old($errorKey, $value);
@endphp

<div class="form-group {{ $wrapperClass }}">
    @if ($label)
        <label class="form-label" for="{{ $id }}">
            {{ $label }}
            @if ($optional)
                <span class="form-label__optional">(opcional)</span>
            @endif
        </label>
    @endif

    <select
        {{ $attributes->class(['form-control', 'is-invalid' => $hasError]) }}
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="form-error" id="{{ $id }}-error">{{ $message }}</p>
    @enderror
</div>
