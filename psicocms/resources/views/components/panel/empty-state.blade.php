@props(['icon' => 'fa-regular fa-folder-open', 'title', 'text' => null, 'compact' => false])

<div {{ $attributes->class(['empty-state', 'empty-state--compact' => $compact]) }}>
    <span class="empty-state__icon"><i class="{{ $icon }}" aria-hidden="true"></i></span>
    <h2 class="empty-state__title">{{ $title }}</h2>
    @if ($text)
        <p class="empty-state__text">{{ $text }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="empty-state__actions">{{ $slot }}</div>
    @endif
</div>
