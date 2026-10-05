@extends('installer.layout')

@section('title', 'Datos de tu web')
@section('heading', 'Los datos que verán tus pacientes')
@section('lead', 'Esta información aparecerá en tu web pública. Rellena lo que tengas a mano; el resto podrás completarlo después.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.fat.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/jodit/jodit.fat.min.js') }}"></script>
@endpush

@php
    $specialtyValues = old('specialties', $specialties);
    $serviceRows = old('services', $services);
    $serviceRows = $serviceRows === [] ? [['title' => '', 'description' => '']] : array_values($serviceRows);
@endphp

@section('content')
    <form method="POST" action="{{ route('installer.public-data') }}" class="installer-form" data-loading-form novalidate>
        @csrf

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-id-card" aria-hidden="true"></i> Presentación</h2>
                <p class="card__subtitle">Cómo te presentarás en tu web.</p>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <x-panel.form.input name="public_name" label="Nombre y apellidos públicos" :value="$site['public_name']" required placeholder="Ej.: Laura Martín Gómez" />
                    <x-panel.form.input name="license_number" label="Número de colegiada" :value="$site['license_number']" optional placeholder="Ej.: M-12345" />
                    <x-panel.form.input name="slogan" label="Frase gancho o eslogan" :value="$site['slogan']" optional wrapper-class="form-group--full" placeholder="Ej.: Te acompaño a recuperar tu bienestar emocional" />
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-phone" aria-hidden="true"></i> Contacto y consulta</h2>
                <p class="card__subtitle">Datos públicos para que tus pacientes puedan pedir cita y encontrarte.</p>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <x-panel.form.input name="booking_phone" label="Teléfono para citas" type="tel" :value="$site['booking_phone']" required icon="fa-solid fa-phone" />
                    <x-panel.form.input name="booking_email" label="Email para citas" type="email" :value="$site['booking_email']" required icon="fa-regular fa-envelope" />
                    <x-panel.form.input name="whatsapp" label="WhatsApp" type="tel" :value="$site['whatsapp']" optional icon="fa-brands fa-whatsapp" hint="Incluye el prefijo del país si quieres que funcione desde el extranjero (ej.: +34)." />
                    <x-panel.form.input name="city" label="Ciudad" :value="$site['city']" optional icon="fa-solid fa-city" />
                    <x-panel.form.input name="address" label="Dirección de la consulta" :value="$site['address']" optional icon="fa-solid fa-location-dot" wrapper-class="form-group--full" placeholder="Calle, número, piso y código postal" />
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-feather" aria-hidden="true"></i> Sobre mí</h2>
                <p class="card__subtitle">Cuéntales quién eres, tu formación y cómo trabajas.</p>
            </div>
            <div class="card__body">
                <x-panel.form.wysiwyg name="about" :value="$site['about']" :height="300" placeholder="Escribe aquí tu presentación…" />
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-tags" aria-hidden="true"></i> Especialidades</h2>
                <p class="card__subtitle">Los temas que trabajas. Escribe una y pulsa “Añadir” o la tecla Intro.</p>
            </div>
            <div class="card__body" data-chips="specialties">
                <div class="chips-input">
                    <label class="sr-only" for="specialty-input">Nueva especialidad</label>
                    <input class="form-control" type="text" id="specialty-input" maxlength="100" placeholder="Ej.: Ansiedad, Duelo, Terapia de pareja…" data-chips-input>
                    <button type="button" class="btn btn--secondary" data-chips-add>
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> Añadir
                    </button>
                </div>
                <div class="chips-list" data-chips-list data-empty="Todavía no has añadido ninguna especialidad.">@foreach ($specialtyValues as $specialty)<span class="chip" data-chip="{{ $specialty }}">{{ $specialty }}<input type="hidden" name="specialties[]" value="{{ $specialty }}"><button type="button" class="chip__remove" aria-label="Quitar {{ $specialty }}" data-chip-remove><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></span>@endforeach</div>
                @error('specialties.*')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i> Servicios principales</h2>
                <p class="card__subtitle">Por ejemplo: terapia individual, terapia de pareja, terapia online…</p>
            </div>
            <div class="card__body repeatable" data-repeatable data-next-index="{{ count($serviceRows) }}" data-max="20">
                <div class="repeatable" data-repeatable-list>
                    @foreach ($serviceRows as $index => $service)
                        <div class="repeatable__row" data-repeatable-row>
                            <x-panel.form.input :name="'services['.$index.'][title]'" label="Servicio" :value="$service['title'] ?? ''" maxlength="150" placeholder="Nombre del servicio" />
                            <x-panel.form.input :name="'services['.$index.'][description]'" label="Descripción breve" :value="$service['description'] ?? ''" optional maxlength="500" placeholder="Una o dos frases" />
                            <button type="button" class="btn btn--icon btn--ghost" aria-label="Quitar servicio" data-repeatable-remove>
                                <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <template data-repeatable-template>
                    <div class="repeatable__row" data-repeatable-row>
                        <div class="form-group">
                            <label class="form-label" for="field-services-__INDEX__-title">Servicio</label>
                            <input class="form-control" type="text" id="field-services-__INDEX__-title" name="services[__INDEX__][title]" maxlength="150" placeholder="Nombre del servicio">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="field-services-__INDEX__-description">Descripción breve <span class="form-label__optional">(opcional)</span></label>
                            <input class="form-control" type="text" id="field-services-__INDEX__-description" name="services[__INDEX__][description]" maxlength="500" placeholder="Una o dos frases">
                        </div>
                        <button type="button" class="btn btn--icon btn--ghost" aria-label="Quitar servicio" data-repeatable-remove>
                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                        </button>
                    </div>
                </template>

                <button type="button" class="btn btn--secondary repeatable__add" data-repeatable-add>
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Añadir otro servicio
                </button>
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="card__title installer-section__title"><i class="fa-solid fa-dollar-sign" aria-hidden="true"></i> Planes y precios</h2>
                <p class="card__subtitle">Precio por sesión. Deja vacío el precio de la modalidad que no ofrezcas.</p>
            </div>
            <div class="card__body">
                <div class="plans-grid">
                    @foreach (['online' => ['Online', 'fa-solid fa-laptop'], 'presencial' => ['Presencial', 'fa-solid fa-couch']] as $modality => [$modalityLabel, $modalityIcon])
                        <div class="plan-box">
                            <h3 class="plan-box__title">
                                <span class="badge badge--{{ $modality }}"><i class="{{ $modalityIcon }}" aria-hidden="true"></i> {{ $modalityLabel }}</span>
                            </h3>
                            <x-panel.form.input :name="'plans['.$modality.'][price]'" label="Precio por sesión" type="number" step="1" min="0" :max="config('psicocms.currency.max_price')" :value="$plans[$modality]['price'] !== null ? (int) $plans[$modality]['price'] : null" :suffix="config('psicocms.currency.code')" optional placeholder="Ej.: 150000" />
                            <x-panel.form.input :name="'plans['.$modality.'][description]'" label="Descripción" :value="$plans[$modality]['description']" optional maxlength="300" placeholder="Ej.: Videollamada segura desde casa" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @include('installer.partials.actions')
    </form>
@endsection
