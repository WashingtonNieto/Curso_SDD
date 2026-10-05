@props(['user', 'size' => 'md'])

@php
    $initials = mb_strtoupper(mb_substr($user->first_name ?? '', 0, 1).mb_substr($user->last_name ?? '', 0, 1));
    $avatarUrl = public_storage_url($user->avatar_path ?? null);
@endphp

<span {{ $attributes->class(['avatar', 'avatar--'.$size]) }}>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="" width="40" height="40">
    @else
        <span aria-hidden="true">{{ $initials }}</span>
    @endif
</span>
