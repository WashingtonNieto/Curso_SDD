<form method="POST" action="{{ $action }}" class="form-stack" data-loading-form novalidate>
    @csrf
    @if ($category->exists)
        @method('PUT')
    @endif

    <x-panel.form.input name="name" label="Nombre" :value="$category->name" required maxlength="100" placeholder="Ej.: Ansiedad y estrés" />

    <div>
        <x-panel.form.input name="slug" label="Dirección (slug)" :value="$category->slug" optional maxlength="120" data-slug-target="field-name" :data-slug-locked="$category->exists" autocomplete="off" :hint="$category->exists ? 'Si la cambias, los enlaces antiguos a esta categoría dejarán de funcionar.' : 'Se genera sola a partir del nombre.'" />
        <p class="slug-preview">Se verá en: {{ url('/blog/categoria') }}/<span class="slug-preview__value" data-slug-preview="field-slug">{{ $category->slug ?: '…' }}</span></p>
    </div>

    <x-panel.form.textarea name="description" label="Descripción" :value="$category->description" optional rows="3" maxlength="500" data-char-counter />

    <div class="form-actions">
        @if ($category->exists)
            <x-panel.button variant="secondary" :href="route('panel.blog.categories.index')">Cancelar</x-panel.button>
        @endif
        <button type="submit" class="btn btn--primary" data-loading-text="Guardando…">
            <i class="btn__icon fa-solid {{ $category->exists ? 'fa-floppy-disk' : 'fa-plus' }}" aria-hidden="true"></i>
            <span class="btn__label">{{ $submitLabel }}</span>
        </button>
    </div>
</form>
