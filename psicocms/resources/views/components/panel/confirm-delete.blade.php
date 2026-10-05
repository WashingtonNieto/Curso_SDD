@props([
    'action',
    'title' => '¿Seguro que quieres eliminarlo?',
    'message' => 'Esta acción no se puede deshacer.',
    'confirmLabel' => 'Sí, eliminar',
    'label' => 'Eliminar',
    'iconOnly' => false,
])

<form method="POST" action="{{ $action }}" class="inline-form"
    data-confirm="{{ $message }}"
    data-confirm-title="{{ $title }}"
    data-confirm-label="{{ $confirmLabel }}">
    @csrf
    @method('DELETE')

    @if ($iconOnly)
        <button type="submit" {{ $attributes->class(['btn', 'btn--icon', 'btn--ghost-danger']) }} aria-label="{{ $label }}" title="{{ $label }}">
            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
        </button>
    @else
        <button type="submit" {{ $attributes->class(['btn', 'btn--soft-danger', 'btn--sm']) }}>
            <i class="btn__icon fa-regular fa-trash-can" aria-hidden="true"></i>
            <span class="btn__label">{{ $label }}</span>
        </button>
    @endif
</form>
