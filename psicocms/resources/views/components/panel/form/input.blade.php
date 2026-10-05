@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'icon' => null,
    'suffix' => null,
    'optional' => false,
    'id' => null,
    'errorKey' => null,
    'wrapperClass' => '',
])

@php
    $errorKey ??= trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= 'field-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $errorKey));
    $hasError = $errors->has($errorKey);
    $fieldValue = $type === 'password' ? null : old($errorKey, $value);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($hasError ? $id.'-error' : ''));
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

    <div class="input-group">
        @if ($icon)
            <i class="input-group__icon {{ $icon }}" aria-hidden="true"></i>
        @endif

        <input
            {{ $attributes->class([
                'form-control',
                'is-invalid' => $hasError,
                'input-group__suffix-target' => $suffix,
                'form-control--with-toggle' => $type === 'password',
            ]) }}
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            @if (! is_null($fieldValue)) value="{{ $fieldValue }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
        >

        @if ($type === 'password')
            <button type="button" class="input-group__toggle" data-password-toggle="{{ $id }}" aria-label="Mostrar contraseña" aria-pressed="false">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </button>
        @endif

        @if ($suffix)
            <span class="input-group__suffix">{{ $suffix }}</span>
        @endif
    </div>

    @if ($hint)
        <p class="form-hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="form-error" id="{{ $id }}-error">{{ $message }}</p>
    @enderror
</div>
