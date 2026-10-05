<?php

return [

    'installed_lock' => storage_path('app/installed.lock'),

    'requirements' => [
        'php' => '8.2.0',
        'extensions' => ['pdo_mysql', 'mbstring', 'gd', 'fileinfo', 'openssl'],
        'writable' => ['storage', 'bootstrap/cache'],
    ],

    'panel_palettes' => [
        'azul' => ['name' => 'Azul sereno', 'color' => '#3D5F8A'],
        'salvia' => ['name' => 'Salvia', 'color' => '#5E8B6E'],
        'lavanda' => ['name' => 'Lavanda', 'color' => '#7B6BA8'],
        'terracota' => ['name' => 'Terracota', 'color' => '#B06A52'],
        'turquesa' => ['name' => 'Turquesa calma', 'color' => '#2F7F86'],
        'malva' => ['name' => 'Malva', 'color' => '#9E6582'],
        'indigo' => ['name' => 'Índigo', 'color' => '#4A55A2'],
        'pizarra' => ['name' => 'Pizarra', 'color' => '#56657A'],
    ],

    'modules' => [
        'blog' => 'Blog',
        'booking' => 'Reservas online',
        'faq' => 'Preguntas frecuentes',
        'services' => 'Servicios',
        'specialties' => 'Especialidades',
        'plans' => 'Planes y precios',
        'whatsapp_float' => 'Botón flotante de WhatsApp',
        'map' => 'Mapa',
    ],

    'image_slots' => [
        'logo' => ['label' => 'Logotipo', 'size' => '300 × 100 px'],
        'favicon' => ['label' => 'Icono de la pestaña (favicon)', 'size' => '64 × 64 px'],
        'hero' => ['label' => 'Imagen principal de portada', 'size' => '1200 × 1400 px'],
        'hero-secundaria' => ['label' => 'Imagen secundaria de portada', 'size' => '800 × 800 px'],
        'sobre-mi' => ['label' => 'Imagen de “Sobre mí”', 'size' => '900 × 1100 px'],
        'servicios-fondo' => ['label' => 'Fondo de servicios', 'size' => '1920 × 900 px'],
        'como-empezar' => ['label' => 'Imagen de “Cómo empezar”', 'size' => '900 × 900 px'],
        'estadisticas-fondo' => ['label' => 'Fondo de estadísticas', 'size' => '1920 × 700 px'],
        'cta-fondo' => ['label' => 'Fondo de llamada a la acción', 'size' => '1920 × 700 px'],
        'footer-forma' => ['label' => 'Forma decorativa del pie', 'size' => '1920 × 300 px'],
        'og' => ['label' => 'Imagen para redes sociales', 'size' => '1200 × 630 px'],
    ],

    'booking' => [
        'min_notice_hours' => 2,
        'max_days_ahead' => 90,
    ],

    'session_duration_presets' => [30, 45, 50, 60],

    'currency' => [
        'code' => 'COP',
        'name' => 'pesos colombianos',
        'symbol' => '$',
        'decimals' => 0,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'max_price' => 999999,
    ],

    'encrypted_settings' => ['mail.app_password'],

    'default_theme' => 'calma',

    'theme_modes' => [
        'landing' => 'Landing (una sola página)',
        'multipage' => 'Multipágina (secciones separadas)',
    ],

];
