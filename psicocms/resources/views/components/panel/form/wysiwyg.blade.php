@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'height' => 400,
    'uploadUrl' => null,
    'withImages' => true,
    'optional' => false,
    'id' => null,
    'errorKey' => null,
    'wrapperClass' => '',
])

@php
    $errorKey ??= trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= 'field-'.\Illuminate\Support\Str::slug(str_replace('.', '-', $errorKey));
    $uploadUrl ??= $withImages && auth()->check() ? route('panel.editor.images') : null;
@endphp

@pushOnce('styles', 'jodit-styles')
    <link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.fat.min.css') }}">
@endPushOnce

@pushOnce('scripts', 'jodit-scripts')
    <script src="{{ asset('vendor/jodit/jodit.fat.min.js') }}"></script>
@endPushOnce

<div class="form-group {{ $wrapperClass }}">
    @if ($label)
        <label class="form-label" for="{{ $id }}">
            {{ $label }}
            @if ($optional)
                <span class="form-label__optional">(opcional)</span>
            @endif
        </label>
    @endif

    <div @class(['wysiwyg', 'is-invalid' => $errors->has($errorKey)])>
        <textarea
            {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($errorKey)]) }}
            id="{{ $id }}"
            name="{{ $name }}"
            data-wysiwyg
            data-height="{{ $height }}"
            @if ($uploadUrl) data-upload-url="{{ $uploadUrl }}" @endif
        >{{ old($errorKey, $value) }}</textarea>
    </div>

    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
