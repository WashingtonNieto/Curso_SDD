<?php

return [

    /*
     * Secciones del panel aún sin implementar: cada fase sustituye su entrada por la ruta real.
     * Clave = nombre de ruta (sin el prefijo "panel.").
     */
    'coming_soon' => [
        'patients.index' => ['uri' => 'pacientes', 'title' => 'Pacientes', 'icon' => 'fa-solid fa-user-group', 'text' => 'La ficha de cada paciente con sus datos, sus citas, su historia clínica y sus documentos.'],
        'clinical.index' => ['uri' => 'historias', 'title' => 'Historias clínicas', 'icon' => 'fa-solid fa-notes-medical', 'text' => 'Las notas de cada sesión, con fotos y PDF adjuntos, guardadas de forma privada.'],
        'blog.posts.index' => ['uri' => 'blog/articulos', 'title' => 'Artículos del blog', 'icon' => 'fa-regular fa-newspaper', 'text' => 'Escribe y publica artículos con imagen y categoría para tu web.'],
        'blog.categories.index' => ['uri' => 'blog/categorias', 'title' => 'Categorías del blog', 'icon' => 'fa-solid fa-tags', 'text' => 'Organiza tus artículos en categorías.'],
        'site.general' => ['uri' => 'mi-web/datos', 'title' => 'Datos generales', 'icon' => 'fa-solid fa-id-card', 'text' => 'Tu nombre público, eslogan, número de colegiada, datos de contacto y dirección de la consulta.'],
        'site.about' => ['uri' => 'mi-web/sobre-mi', 'title' => 'Sobre mí', 'icon' => 'fa-solid fa-feather', 'text' => 'Tu presentación, tu foto y tu formación y experiencia.'],
        'site.services' => ['uri' => 'mi-web/servicios', 'title' => 'Servicios', 'icon' => 'fa-solid fa-hand-holding-heart', 'text' => 'Los servicios que ofreces, con icono, imagen y descripción.'],
        'site.specialties' => ['uri' => 'mi-web/especialidades', 'title' => 'Especialidades', 'icon' => 'fa-solid fa-seedling', 'text' => 'Los temas en los que estás especializada.'],
        'site.plans' => ['uri' => 'mi-web/planes', 'title' => 'Planes y precios', 'icon' => 'fa-solid fa-dollar-sign', 'text' => 'Tus tarifas online y presenciales.'],
        'site.faqs' => ['uri' => 'mi-web/preguntas-frecuentes', 'title' => 'Preguntas frecuentes', 'icon' => 'fa-regular fa-circle-question', 'text' => 'Las dudas habituales de tus pacientes, ordenadas como quieras.'],
        'site.images' => ['uri' => 'mi-web/imagenes', 'title' => 'Imágenes', 'icon' => 'fa-regular fa-images', 'text' => 'Cambia las imágenes de ejemplo de tu web por las tuyas.'],
        'site.phrases' => ['uri' => 'mi-web/frases', 'title' => 'Frases públicas', 'icon' => 'fa-solid fa-quote-left', 'text' => 'Personaliza todos los textos que aparecen en tu web.'],
        'site.social' => ['uri' => 'mi-web/redes-sociales', 'title' => 'Redes sociales', 'icon' => 'fa-solid fa-share-nodes', 'text' => 'Los enlaces a tus redes sociales que aparecerán en tu web.'],
        'site.themes' => ['uri' => 'mi-web/temas', 'title' => 'Temas visuales', 'icon' => 'fa-solid fa-palette', 'text' => 'Elige entre 5 diseños para tu web, en formato landing o multipágina.'],
        'site.seo' => ['uri' => 'mi-web/seo', 'title' => 'SEO', 'icon' => 'fa-solid fa-magnifying-glass-chart', 'text' => 'Cómo aparece tu web en Google y en las redes sociales.'],
        'site.legal' => ['uri' => 'mi-web/textos-legales', 'title' => 'Textos legales', 'icon' => 'fa-solid fa-scale-balanced', 'text' => 'Tu política de privacidad.'],
        'settings.general' => ['uri' => 'configuracion/general', 'title' => 'Mi perfil', 'icon' => 'fa-solid fa-user-gear', 'text' => 'Tus datos privados de acceso, tu contraseña y tu avatar.'],
        'settings.modules' => ['uri' => 'configuracion/modulos', 'title' => 'Módulos de la web', 'icon' => 'fa-solid fa-toggle-on', 'text' => 'Activa o desactiva partes de tu web: blog, reservas, preguntas frecuentes…'],
        'settings.email' => ['uri' => 'configuracion/email', 'title' => 'Email y notificaciones', 'icon' => 'fa-regular fa-envelope', 'text' => 'Recibe un email cada vez que un paciente reserve una cita.'],
        'settings.privacy' => ['uri' => 'configuracion/proteccion-datos', 'title' => 'Protección de datos', 'icon' => 'fa-solid fa-file-shield', 'text' => 'Tu plantilla de protección de datos y el PDF para cada paciente.'],
        'search' => ['uri' => 'buscar', 'title' => 'Buscar', 'icon' => 'fa-solid fa-magnifying-glass', 'text' => 'Encuentra al instante pacientes, citas, historias, artículos y mucho más.'],
        'help' => ['uri' => 'ayuda', 'title' => 'Ayuda', 'icon' => 'fa-regular fa-life-ring', 'text' => 'Un tutorial sencillo, paso a paso, para sacar todo el partido a tu panel.'],
    ],

    'menu' => [
        ['label' => 'Inicio', 'icon' => 'fa-solid fa-house', 'route' => 'panel.home', 'match' => 'panel.home'],
        ['label' => 'Citas', 'icon' => 'fa-regular fa-calendar-check', 'route' => 'panel.appointments.index', 'match' => 'panel.appointments.*'],
        ['label' => 'Calendario', 'icon' => 'fa-regular fa-calendar', 'route' => 'panel.calendar', 'match' => 'panel.calendar*'],
        ['label' => 'Pacientes', 'icon' => 'fa-solid fa-user-group', 'route' => 'panel.patients.index', 'match' => 'panel.patients.*'],
        ['label' => 'Historias clínicas', 'icon' => 'fa-solid fa-notes-medical', 'route' => 'panel.clinical.index', 'match' => 'panel.clinical.*'],
        ['label' => 'Disponibilidad', 'icon' => 'fa-regular fa-clock', 'route' => 'panel.availability', 'match' => 'panel.availability*'],
        [
            'label' => 'Blog',
            'icon' => 'fa-regular fa-newspaper',
            'id' => 'blog',
            'children' => [
                ['label' => 'Artículos', 'route' => 'panel.blog.posts.index', 'match' => 'panel.blog.posts.*'],
                ['label' => 'Categorías', 'route' => 'panel.blog.categories.index', 'match' => 'panel.blog.categories.*'],
            ],
        ],
        [
            'label' => 'Mi web',
            'icon' => 'fa-solid fa-globe',
            'id' => 'mi-web',
            'children' => [
                ['label' => 'Datos generales', 'route' => 'panel.site.general', 'match' => 'panel.site.general*'],
                ['label' => 'Sobre mí', 'route' => 'panel.site.about', 'match' => 'panel.site.about*'],
                ['label' => 'Servicios', 'route' => 'panel.site.services', 'match' => 'panel.site.services*'],
                ['label' => 'Especialidades', 'route' => 'panel.site.specialties', 'match' => 'panel.site.specialties*'],
                ['label' => 'Planes y precios', 'route' => 'panel.site.plans', 'match' => 'panel.site.plans*'],
                ['label' => 'Preguntas frecuentes', 'route' => 'panel.site.faqs', 'match' => 'panel.site.faqs*'],
                ['label' => 'Imágenes', 'route' => 'panel.site.images', 'match' => 'panel.site.images*'],
                ['label' => 'Frases públicas', 'route' => 'panel.site.phrases', 'match' => 'panel.site.phrases*'],
                ['label' => 'Redes sociales', 'route' => 'panel.site.social', 'match' => 'panel.site.social*'],
                ['label' => 'Temas visuales', 'route' => 'panel.site.themes', 'match' => 'panel.site.themes*'],
                ['label' => 'SEO', 'route' => 'panel.site.seo', 'match' => 'panel.site.seo*'],
                ['label' => 'Textos legales', 'route' => 'panel.site.legal', 'match' => 'panel.site.legal*'],
            ],
        ],
        [
            'label' => 'Configuración',
            'icon' => 'fa-solid fa-gear',
            'id' => 'configuracion',
            'children' => [
                ['label' => 'General / Mi perfil', 'route' => 'panel.settings.general', 'match' => 'panel.settings.general*'],
                ['label' => 'Módulos de la web', 'route' => 'panel.settings.modules', 'match' => 'panel.settings.modules*'],
                ['label' => 'Email y notificaciones', 'route' => 'panel.settings.email', 'match' => 'panel.settings.email*'],
                ['label' => 'Protección de datos', 'route' => 'panel.settings.privacy', 'match' => 'panel.settings.privacy*'],
            ],
        ],
    ],

];
