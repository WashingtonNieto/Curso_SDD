@php
    $flash = session('toast');
    $icons = ['success' => 'fa-solid fa-circle-check', 'error' => 'fa-solid fa-circle-exclamation', 'info' => 'fa-solid fa-circle-info'];
@endphp

<div class="toast-stack" role="status" aria-live="polite">
    @if (is_array($flash) && ! empty($flash['message']))
        @php $type = $flash['type'] ?? 'success'; @endphp
        <div class="toast toast--{{ $type }}">
            <i class="toast__icon {{ $icons[$type] ?? $icons['info'] }}" aria-hidden="true"></i>
            <p class="toast__message">{{ $flash['message'] }}</p>
            <button type="button" class="toast__close" aria-label="Cerrar aviso"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
    @endif
</div>
