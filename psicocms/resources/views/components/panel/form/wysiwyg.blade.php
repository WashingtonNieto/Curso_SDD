@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'height' => 360,
    'uploadUrl' => null,
    'optional' => false,
    'id' => null,
    'errorKey' => null,
    'wrapperClass' => '',
])

@php
    $errorKey ??= trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= 'field-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $errorKey));
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
        {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($errorKey)]) }}
        id="{{ $id }}"
        name="{{ $name }}"
        data-wysiwyg
        data-height="{{ $height }}"
        @if ($uploadUrl) data-upload-url="{{ $uploadUrl }}" @endif
    >{{ old($errorKey, $value) }}</textarea>

    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
