<div class="installer-actions">
    @if ($previousStep)
        <a class="btn btn--secondary btn--lg" href="{{ route('installer.show', $previousStep) }}">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Atrás
        </a>
    @endif

    <button type="submit" class="btn btn--primary btn--lg installer-actions__end" data-loading-text="{{ $loadingText ?? 'Guardando…' }}">
        <span class="btn__label">{{ $submitLabel ?? 'Guardar y continuar' }}</span>
        <i class="btn__icon fa-solid {{ $submitIcon ?? 'fa-arrow-right' }}" aria-hidden="true"></i>
    </button>
</div>
