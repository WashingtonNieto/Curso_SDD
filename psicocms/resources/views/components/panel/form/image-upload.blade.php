@props([
    'name',
    'label' => null,
    'current' => null,
    'hint' => null,
    'accept' => 'image/jpeg,image/png,image/webp',
    'maxMb' => 4,
    'buttonLabel' => 'Elegir imagen',
    'placeholderIcon' => 'fa-regular fa-image',
    'id' => null,
    'wrapperClass' => '',
])

@php
    $id ??= 'field-'.\Illuminate\Support\Str::slug($name);
@endphp

<div class="form-group">
    @if ($label)
        <span class="form-label">{{ $label }}</span>
    @endif

    <div class="image-upload {{ $wrapperClass }}" data-image-upload data-max-bytes="{{ $maxMb * 1024 * 1024 }}">
        <div class="image-upload__preview" data-image-preview>
            @if ($current)
                <img src="{{ $current }}" alt="Imagen actual">
            @else
                <i class="{{ $placeholderIcon }}" aria-hidden="true"></i>
            @endif
        </div>

        <div class="image-upload__actions">
            <input {{ $attributes->class('image-upload__input') }} type="file" id="{{ $id }}" name="{{ $name }}" accept="{{ $accept }}">
            <label for="{{ $id }}" class="btn btn--secondary">
                <i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i>
                {{ $current ? 'Cambiar imagen' : $buttonLabel }}
            </label>
            <span class="form-hint" data-image-name>{{ $hint }}</span>
            <p class="form-error" data-image-error hidden></p>
            {{ $slot }}
        </div>
    </div>

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
