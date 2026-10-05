@props(['id', 'title', 'icon' => null, 'size' => 'md'])

<dialog {{ $attributes->class(['modal', 'modal--'.$size]) }} id="{{ $id }}" aria-labelledby="{{ $id }}-title" data-modal>
    <div class="modal__dialog">
        <header class="modal__header">
            <h2 class="modal__title" id="{{ $id }}-title">
                @if ($icon)
                    <i class="modal__title-icon {{ $icon }}" aria-hidden="true"></i>
                @endif
                {{ $title }}
            </h2>
            <button type="button" class="modal__close" data-modal-close aria-label="Cerrar ventana">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="modal__body">
            {{ $slot }}
        </div>

        @isset($footer)
            <footer class="modal__footer">{{ $footer }}</footer>
        @endisset
    </div>
</dialog>
