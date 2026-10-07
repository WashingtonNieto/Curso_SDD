@use('App\Models\BlogPost')
@use('App\Http\Requests\Panel\BlogPostRequest')

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="post-form" data-loading-form novalidate>
    @csrf
    @if ($post->exists)
        @method('PUT')
    @endif

    <div class="form-layout">
        <div class="form-layout__main">
            <x-panel.card title="Contenido" icon="fa-regular fa-file-lines">
                <div class="form-stack">
                    <x-panel.form.input name="title" label="Título" :value="$post->title" required maxlength="200" placeholder="Ej.: 5 claves para gestionar la ansiedad en el trabajo" />

                    <div>
                        <x-panel.form.input name="slug" label="Dirección del artículo (slug)" :value="$post->slug" maxlength="200" optional data-slug-target="field-title" :data-slug-locked="$post->exists" autocomplete="off" :hint="$post->exists ? 'Si la cambias, los enlaces antiguos a este artículo dejarán de funcionar.' : 'Se genera sola a partir del título. Solo cámbiala si lo necesitas.'" />
                        <p class="slug-preview">Se verá en: {{ url('/blog') }}/<span class="slug-preview__value" data-slug-preview="field-slug">{{ $post->slug ?: '…' }}</span></p>
                    </div>

                    <x-panel.form.textarea name="excerpt" label="Extracto" :value="$post->excerpt" optional rows="3" maxlength="500" data-char-counter hint="Un resumen breve que se muestra en el listado del blog." />

                    <x-panel.form.wysiwyg name="content" label="Contenido del artículo" :value="$post->content" placeholder="Empieza a escribir tu artículo…" />
                </div>
            </x-panel.card>
        </div>

        <div class="form-layout__side">
            <x-panel.card title="Publicación" icon="fa-regular fa-paper-plane">
                <div class="form-stack">
                    <x-panel.form.select name="status" label="Estado" :options="BlogPost::STATUSES" :value="$post->status" hint="Los borradores no se muestran en tu web." />
                    <x-panel.form.input name="published_at" type="datetime-local" label="Fecha de publicación" :value="$post->published_at?->format('Y-m-d\TH:i')" optional hint="Si la dejas vacía al publicar, se usará la fecha actual. Si eliges una fecha futura, el artículo aparecerá ese día." />
                </div>
            </x-panel.card>

            <x-panel.card title="Categoría" icon="fa-solid fa-tags">
                <div class="field-row">
                    <x-panel.form.select name="blog_category_id" label="Categoría" :options="$categoryOptions" :value="$post->blog_category_id" placeholder="Sin categoría" data-category-select />
                    <button type="button" class="btn btn--secondary btn--icon" data-modal-open="category-modal" aria-label="Crear una categoría nueva" title="Nueva categoría">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </button>
                </div>
            </x-panel.card>

            <x-panel.card title="Imagen destacada" icon="fa-regular fa-image">
                <x-panel.form.image-upload name="image" :current="public_storage_url($post->image_path)" hint="JPG, PNG o WEBP. Máximo 4 MB. Recomendado: 1200 × 800 px." wrapper-class="image-upload--wide">
                    @if ($post->image_path)
                        <label class="check">
                            <input class="check__input" type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>
                            <span>Quitar la imagen actual</span>
                        </label>
                    @endif
                </x-panel.form.image-upload>
            </x-panel.card>

            <x-panel.card title="SEO" icon="fa-solid fa-magnifying-glass-chart">
                <x-panel.form.textarea name="meta_description" label="Meta descripción" :value="$post->meta_description" optional rows="3" :maxlength="BlogPostRequest::META_MAX" data-char-counter hint="El texto que aparece bajo el título en Google. Si la dejas vacía se usará el extracto." />
            </x-panel.card>

            <div class="form-actions">
                <x-panel.button variant="secondary" :href="route('panel.blog.posts.index')">Cancelar</x-panel.button>
                <button type="submit" class="btn btn--primary" data-loading-text="Guardando…">
                    <i class="btn__icon fa-regular fa-floppy-disk" aria-hidden="true"></i>
                    <span class="btn__label">{{ $submitLabel }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

<x-panel.modal id="category-modal" title="Nueva categoría" icon="fa-solid fa-tags" size="sm">
    <form method="POST" action="{{ route('panel.blog.categories.store') }}" class="form-stack" data-category-quick-form novalidate>
        <x-panel.form.input name="name" label="Nombre" id="quick-category-name" required maxlength="100" error-key="quick_name" />
        <x-panel.form.textarea name="description" label="Descripción" id="quick-category-description" optional rows="2" maxlength="500" error-key="quick_description" />
        <p class="form-error" data-category-error hidden></p>
        <div class="form-actions">
            <button type="button" class="btn btn--secondary" data-modal-close>Cancelar</button>
            <button type="submit" class="btn btn--primary">
                <i class="btn__icon fa-solid fa-plus" aria-hidden="true"></i>
                <span class="btn__label">Crear categoría</span>
            </button>
        </div>
    </form>
</x-panel.modal>
