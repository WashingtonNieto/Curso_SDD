@use('App\Models\Appointment')
@use('App\Models\Patient')

@php
    $duplicate = session('duplicate_patient_id') ? Patient::find(session('duplicate_patient_id')) : null;
@endphp

@if ($duplicate)
    <div class="alert alert--warning patient-duplicate" role="alert">
        <i class="alert__icon fa-solid fa-user-check" aria-hidden="true"></i>
        <div>
            <strong class="alert__title">Este teléfono ya pertenece a {{ $duplicate->full_name }}</strong>
            <p>El teléfono identifica a cada paciente, así que no puede repetirse. Puedes abrir su ficha para revisar o completar sus datos.</p>
            <a class="btn btn--secondary btn--sm patient-duplicate__link" href="{{ route('panel.patients.show', $duplicate) }}">
                <i class="btn__icon fa-regular fa-eye" aria-hidden="true"></i>
                <span class="btn__label">Ver la ficha de {{ $duplicate->first_name }}</span>
            </a>
        </div>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="patient-form" data-loading-form novalidate>
    @csrf
    @if ($patient->exists)
        @method('PUT')
    @endif

    <div class="form-layout">
        <div class="form-layout__main">
            <x-panel.card title="Datos de contacto" icon="fa-regular fa-address-card" subtitle="El teléfono es obligatorio: identifica a cada paciente y se guarda sin espacios.">
                <div class="form-grid">
                    <x-panel.form.input name="first_name" label="Nombre" :value="$patient->first_name" required maxlength="100" autocomplete="off" />
                    <x-panel.form.input name="last_name" label="Apellidos" :value="$patient->last_name" optional maxlength="150" autocomplete="off" />
                    <x-panel.form.input name="phone" type="tel" label="Teléfono" :value="$patient->phone" required icon="fa-solid fa-phone" autocomplete="off" />
                    <x-panel.form.input name="email" type="email" label="Email" :value="$patient->email" optional icon="fa-regular fa-envelope" autocomplete="off" />
                </div>
            </x-panel.card>

            <x-panel.card title="Datos personales" icon="fa-regular fa-user">
                <div class="form-grid">
                    <x-panel.form.input name="birth_date" type="date" label="Fecha de nacimiento" :value="$patient->birth_date?->toDateString()" optional :max="today()->toDateString()" />
                    <x-panel.form.select name="gender" label="Género" :options="Patient::GENDERS" :value="$patient->gender" placeholder="Sin indicar" optional />
                    <x-panel.form.input name="dni" label="Documento de identidad" :value="$patient->dni" optional maxlength="20" hint="DNI, NIE, cédula o pasaporte." />
                    <x-panel.form.input name="occupation" label="Ocupación" :value="$patient->occupation" optional maxlength="150" />
                    <x-panel.form.input name="address" label="Dirección" :value="$patient->address" optional maxlength="255" wrapper-class="form-group--full" />
                    <x-panel.form.input name="city" label="Ciudad" :value="$patient->city" optional maxlength="100" />
                    <x-panel.form.input name="postal_code" label="Código postal" :value="$patient->postal_code" optional maxlength="10" />
                </div>
            </x-panel.card>

            <x-panel.card title="Contacto de emergencia" icon="fa-solid fa-life-ring">
                <div class="form-grid">
                    <x-panel.form.input name="emergency_contact_name" label="Nombre y relación" :value="$patient->emergency_contact_name" optional maxlength="150" placeholder="Ej.: Ana López (hermana)" />
                    <x-panel.form.input name="emergency_contact_phone" type="tel" label="Teléfono" :value="$patient->emergency_contact_phone" optional icon="fa-solid fa-phone" />
                </div>
            </x-panel.card>

            <x-panel.card title="Notas" icon="fa-regular fa-note-sticky" subtitle="Información general sobre el paciente. Las notas de cada sesión irán en su historia clínica.">
                <x-panel.form.wysiwyg name="notes" :value="$patient->notes" :height="300" :with-images="false" placeholder="Escribe aquí tus notas…" />
            </x-panel.card>
        </div>

        <div class="form-layout__side">
            <x-panel.card title="Terapia" icon="fa-solid fa-hand-holding-heart">
                <div class="form-stack">
                    <x-panel.form.select name="status" label="Estado" :options="Patient::STATUSES" :value="$patient->status" />
                    <x-panel.form.select name="preferred_modality" label="Modalidad preferida" :options="Appointment::MODALITIES" :value="$patient->preferred_modality" placeholder="Sin preferencia" optional />
                    <x-panel.form.input name="therapy_type" label="Enfoque o tipo de terapia" :value="$patient->therapy_type" optional maxlength="150" placeholder="Ej.: Terapia cognitivo-conductual" />
                    <x-panel.form.textarea name="reason" label="Motivo de consulta" :value="$patient->reason" optional rows="4" maxlength="2000" data-char-counter />
                </div>
            </x-panel.card>

            <div class="form-actions">
                <x-panel.button variant="secondary" :href="$patient->exists ? route('panel.patients.show', $patient) : route('panel.patients.index')">Cancelar</x-panel.button>
                <button type="submit" class="btn btn--primary" data-loading-text="Guardando…">
                    <i class="btn__icon fa-regular fa-floppy-disk" aria-hidden="true"></i>
                    <span class="btn__label">{{ $submitLabel }}</span>
                </button>
            </div>
        </div>
    </div>
</form>
