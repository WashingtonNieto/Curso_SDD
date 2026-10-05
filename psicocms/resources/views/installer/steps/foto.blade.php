@extends('installer.layout')

@section('title', 'Tu foto')
@section('heading', 'Tu foto profesional')
@section('lead', 'Una buena foto transmite cercanía y confianza. Aparecerá en la portada y en la sección “Sobre mí”.')

@section('content')
    <form method="POST" action="{{ route('installer.photo') }}" enctype="multipart/form-data" class="installer-form" data-loading-form novalidate>
        @csrf

        <section class="card">
            <div class="card__body">
                <x-panel.form.image-upload
                    name="photo"
                    :current="$photoUrl"
                    button-label="Elegir foto"
                    placeholder-icon="fa-solid fa-user-tie"
                    wrapper-class="photo-upload"
                    hint="JPG, PNG o WEBP · máximo 4 MB"
                >
                    <ul class="photo-tips">
                        <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <span><strong>Preferiblemente en PNG y sin fondo</strong> (fondo transparente): quedará integrada en el diseño de tu web.</span></li>
                        <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <span>Con buena luz, mirando a cámara y con una expresión cercana.</span></li>
                        <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <span>Mejor en vertical, de cintura para arriba.</span></li>
                    </ul>
                </x-panel.form.image-upload>
            </div>
        </section>

        <div class="alert alert--info">
            <i class="alert__icon fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>Este paso es opcional. Si ahora no tienes una foto a mano, continúa y súbela más tarde desde tu panel.</div>
        </div>

        @include('installer.partials.actions')
    </form>
@endsection
