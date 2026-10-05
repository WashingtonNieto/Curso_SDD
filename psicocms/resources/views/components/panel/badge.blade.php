@props(['type' => null, 'value' => null, 'tone' => null, 'icon' => null])

@php
    $map = [
        'modality' => [
            'online' => ['Online', 'online', 'fa-solid fa-video'],
            'presencial' => ['Presencial', 'presencial', 'fa-solid fa-location-dot'],
        ],
        'appointment' => [
            'pendiente' => ['Pendiente', 'warning', null],
            'confirmada' => ['Confirmada', 'primary', null],
            'completada' => ['Completada', 'success', null],
            'cancelada' => ['Cancelada', 'danger', null],
            'no_asistio' => ['No asistió', 'neutral', null],
        ],
        'patient' => [
            'activo' => ['Activo', 'success', null],
            'pausado' => ['En pausa', 'warning', null],
            'alta' => ['Alta', 'neutral', null],
        ],
        'post' => [
            'publicado' => ['Publicado', 'primary', 'fa-solid fa-circle'],
            'borrador' => ['Borrador', 'success', 'fa-solid fa-pen'],
        ],
    ];

    [$label, $mappedTone, $mappedIcon] = $map[$type][$value] ?? [trim($slot) !== '' ? null : $value, 'neutral', null];
    $tone ??= $mappedTone;
    $icon ??= $mappedIcon;
@endphp

<span {{ $attributes->class(['badge', 'badge--'.$tone]) }}>
    @if ($icon)
        <i class="badge__icon {{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $label ?? $slot }}
</span>
