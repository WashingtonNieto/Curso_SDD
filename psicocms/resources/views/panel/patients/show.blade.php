@extends('panel.layout')

@section('title', $patient->full_name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('panel/css/pages/patients.css') }}">
@endpush

@use('App\Models\Appointment')
@use('App\Models\Patient')

@php
    $whatsapp = $patient->phone ? ltrim($patient->phone, '+') : null;
    $empty = '—';
    $newAppointmentUrl = route('panel.appointments.create', ['paciente' => $patient->id]);
    $appointmentsCount = $upcoming->count() + $past->count();
@endphp

@section('content')
    <header class="page-header patient-header">
        <div class="patient-header__identity">
            <span class="avatar avatar--lg" aria-hidden="true">{{ $patient->initials }}</span>
            <div class="page-header__text">
                <h1 class="page-header__title">{{ $patient->full_name }}</h1>
                <p class="patient-header__meta">
                    <x-panel.badge type="patient" :value="$patient->status" />
                    @if ($patient->preferred_modality)
                        <x-panel.badge type="modality" :value="$patient->preferred_modality" />
                    @endif
                    <span class="patient-header__phone"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $patient->phone }}</span>
                </p>
            </div>
        </div>

        <div class="page-header__actions">
            <x-panel.button :href="$newAppointmentUrl" icon="fa-regular fa-calendar-plus">Nueva cita</x-panel.button>
            <x-panel.button variant="secondary" :href="route('panel.patients.edit', $patient)" icon="fa-regular fa-pen-to-square">Editar</x-panel.button>
            <x-panel.confirm-delete :action="route('panel.patients.destroy', $patient)" title="¿Eliminar la ficha de este paciente?" :message="$deleteMessage" label="Eliminar" />
        </div>
    </header>

    <div class="stats-grid">
        <x-panel.stat-card label="Sesiones realizadas" :value="$summary['sessions']" icon="fa-solid fa-couch" tone="primary" hint="Citas pasadas confirmadas o completadas" />
        <x-panel.stat-card label="Última cita" :value="$summary['last']?->starts_at->translatedFormat('j M Y') ?? 'Ninguna'" icon="fa-regular fa-calendar-check" tone="success" :hint="$summary['last'] ? $summary['last']->starts_at->format('H:i').' · '.Appointment::MODALITIES[$summary['last']->modality] : null" />
        <x-panel.stat-card label="Próxima cita" :value="$summary['next']?->starts_at->translatedFormat('j M Y') ?? 'Sin programar'" icon="fa-regular fa-clock" tone="info" :hint="$summary['next'] ? $summary['next']->starts_at->format('H:i').' · '.Appointment::MODALITIES[$summary['next']->modality] : null" :href="$summary['next'] ? route('panel.appointments.edit', $summary['next']) : $newAppointmentUrl" />
    </div>

    <x-panel.tabs id="ficha" :active="$tab" :tabs="[
        'datos' => ['label' => 'Datos', 'icon' => 'fa-regular fa-address-card'],
        'citas' => ['label' => 'Citas ('.$appointmentsCount.')', 'icon' => 'fa-regular fa-calendar'],
        'historia' => ['label' => 'Historia clínica', 'icon' => 'fa-solid fa-notes-medical'],
        'documentos' => ['label' => 'Documentos', 'icon' => 'fa-regular fa-file-lines'],
    ]">
        <x-panel.tab-panel tabs="ficha" name="datos" :active="$tab === 'datos'">
            <div class="patient-details">
                <x-panel.card title="Contacto" icon="fa-regular fa-address-card">
                    <dl class="detail-list">
                        <div class="detail-list__item">
                            <dt>Teléfono</dt>
                            <dd>
                                <a href="tel:{{ $patient->phone }}">{{ $patient->phone }}</a>
                                <span class="detail-list__actions">
                                    <a class="btn btn--sm btn--ghost" href="tel:{{ $patient->phone }}"><i class="btn__icon fa-solid fa-phone" aria-hidden="true"></i><span class="btn__label">Llamar</span></a>
                                    <a class="btn btn--sm btn--ghost" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"><i class="btn__icon fa-brands fa-whatsapp" aria-hidden="true"></i><span class="btn__label">WhatsApp</span></a>
                                </span>
                            </dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Email</dt>
                            <dd>
                                @if ($patient->email)
                                    <a href="mailto:{{ $patient->email }}">{{ $patient->email }}</a>
                                @else
                                    {{ $empty }}
                                @endif
                            </dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Contacto de emergencia</dt>
                            <dd>
                                {{ $patient->emergency_contact_name ?: $empty }}
                                @if ($patient->emergency_contact_phone)
                                    · <a href="tel:{{ $patient->emergency_contact_phone }}">{{ $patient->emergency_contact_phone }}</a>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </x-panel.card>

                <x-panel.card title="Datos personales" icon="fa-regular fa-user">
                    <dl class="detail-list">
                        <div class="detail-list__item">
                            <dt>Fecha de nacimiento</dt>
                            <dd>
                                @if ($patient->birth_date)
                                    {{ $patient->birth_date->translatedFormat('j \d\e F \d\e Y') }} ({{ $patient->birth_date->age }} años)
                                @else
                                    {{ $empty }}
                                @endif
                            </dd>
                        </div>
                        <div class="detail-list__item"><dt>Género</dt><dd>{{ Patient::GENDERS[$patient->gender] ?? $empty }}</dd></div>
                        <div class="detail-list__item"><dt>Documento de identidad</dt><dd>{{ $patient->dni ?: $empty }}</dd></div>
                        <div class="detail-list__item"><dt>Ocupación</dt><dd>{{ $patient->occupation ?: $empty }}</dd></div>
                        <div class="detail-list__item">
                            <dt>Dirección</dt>
                            <dd>{{ collect([$patient->address, trim($patient->postal_code.' '.$patient->city)])->filter()->implode(', ') ?: $empty }}</dd>
                        </div>
                    </dl>
                </x-panel.card>

                <x-panel.card title="Terapia" icon="fa-solid fa-hand-holding-heart">
                    <dl class="detail-list">
                        <div class="detail-list__item"><dt>Enfoque o tipo de terapia</dt><dd>{{ $patient->therapy_type ?: $empty }}</dd></div>
                        <div class="detail-list__item"><dt>Motivo de consulta</dt><dd class="detail-list__text">{{ $patient->reason ?: $empty }}</dd></div>
                        <div class="detail-list__item">
                            <dt>Cómo llegó</dt>
                            <dd>{{ $patient->source === 'web' ? 'Reserva desde la web' : 'Añadido a mano' }} · {{ $patient->created_at?->translatedFormat('j M Y') }}</dd>
                        </div>
                    </dl>
                </x-panel.card>

                <x-panel.card title="Notas" icon="fa-regular fa-note-sticky">
                    @if ($patient->notes)
                        <div class="rich-text">{!! $patient->notes !!}</div>
                    @else
                        <x-panel.empty-state compact icon="fa-regular fa-note-sticky" title="Sin notas" text="Puedes añadir notas generales editando la ficha." />
                    @endif
                </x-panel.card>
            </div>
        </x-panel.tab-panel>

        <x-panel.tab-panel tabs="ficha" name="citas" :active="$tab === 'citas'">
            <div class="patient-appointments">
                <x-panel.card flush title="Próximas citas" icon="fa-regular fa-calendar-plus">
                    <x-slot:actions>
                        <x-panel.button size="sm" :href="$newAppointmentUrl" icon="fa-solid fa-plus">Nueva cita para este paciente</x-panel.button>
                    </x-slot:actions>

                    @if ($upcoming->isEmpty())
                        <x-panel.empty-state compact icon="fa-regular fa-calendar" title="No tiene citas programadas" text="Cuando le des una nueva cita aparecerá aquí." />
                    @else
                        @include('panel.patients._appointments-table', ['appointments' => $upcoming, 'caption' => 'Próximas citas'])
                    @endif
                </x-panel.card>

                <x-panel.card flush title="Citas anteriores" icon="fa-solid fa-clock-rotate-left">
                    @if ($past->isEmpty())
                        <x-panel.empty-state compact icon="fa-solid fa-clock-rotate-left" title="Todavía no hay citas anteriores" />
                    @else
                        @include('panel.patients._appointments-table', ['appointments' => $past, 'caption' => 'Citas anteriores'])
                    @endif
                </x-panel.card>
            </div>
        </x-panel.tab-panel>

        <x-panel.tab-panel tabs="ficha" name="historia" :active="$tab === 'historia'">
            <x-panel.card>
                <x-panel.empty-state icon="fa-solid fa-notes-medical" title="Historia clínica" text="Muy pronto podrás escribir aquí las notas de cada sesión y adjuntar fotos o PDF escaneados, guardados de forma privada." />
            </x-panel.card>
        </x-panel.tab-panel>

        <x-panel.tab-panel tabs="ficha" name="documentos" :active="$tab === 'documentos'">
            <x-panel.card>
                <x-panel.empty-state icon="fa-solid fa-file-shield" title="Documentos" text="Muy pronto podrás descargar aquí el documento de protección de datos relleno con los datos de este paciente." />
            </x-panel.card>
        </x-panel.tab-panel>
    </x-panel.tabs>
@endsection
