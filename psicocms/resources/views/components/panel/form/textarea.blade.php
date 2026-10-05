@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'optional' => false,
    'rows' => 4,
    'id' => null,
    'errorKey' => null,
    'wrapperClass' => '',
])

@php
    $errorKey ??= trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= 'field-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $errorKey));
    $hasError = $errors->has($errorKey);
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

    <textarea
        {{ $attributes->class(['form-control', 'is-invalid' => $hasError]) }}
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    >{{ old($errorKey, $value) }}</textarea>

    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="form-error" id="{{ $id }}-error">{{ $message }}</p>
    @enderror
</div>
