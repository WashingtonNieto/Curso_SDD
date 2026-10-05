<?php

namespace App\Services\Installer;

class RequirementsChecker
{
    private const EXTENSION_LABELS = [
        'pdo_mysql' => 'Conexión con MySQL (pdo_mysql)',
        'mbstring' => 'Textos con tildes y eñes (mbstring)',
        'gd' => 'Tratamiento de imágenes y PDF (gd)',
        'fileinfo' => 'Detección de tipos de archivo (fileinfo)',
        'openssl' => 'Cifrado seguro (openssl)',
    ];

    public function checks(): array
    {
        $requirements = config('psicocms.requirements');
        $checks = [[
            'group' => 'Servidor',
            'label' => 'PHP '.$requirements['php'].' o superior',
            'detail' => 'Versión instalada: '.PHP_VERSION,
            'ok' => version_compare(PHP_VERSION, $requirements['php'], '>='),
        ]];

        foreach ($requirements['extensions'] as $extension) {
            $loaded = extension_loaded($extension);
            $checks[] = [
                'group' => 'Extensiones de PHP',
                'label' => self::EXTENSION_LABELS[$extension] ?? $extension,
                'detail' => $loaded ? 'Activada' : 'Actívala en el archivo php.ini (extension='.$extension.') y reinicia el servidor.',
                'ok' => $loaded,
            ];
        }

        foreach ($requirements['writable'] as $folder) {
            $writable = is_writable(base_path($folder));
            $checks[] = [
                'group' => 'Permisos de escritura',
                'label' => 'Carpeta '.$folder.'/',
                'detail' => $writable ? 'Se puede escribir' : 'Da permisos de escritura a esta carpeta.',
                'ok' => $writable,
            ];
        }

        $envPath = base_path('.env');
        $envWritable = is_file($envPath) ? is_writable($envPath) : is_writable(base_path());
        $checks[] = [
            'group' => 'Permisos de escritura',
            'label' => 'Archivo de configuración .env',
            'detail' => $envWritable ? 'Se puede escribir' : 'Da permisos de escritura al archivo .env.',
            'ok' => $envWritable,
        ];

        return $checks;
    }

    public function passes(): bool
    {
        return collect($this->checks())->every(fn (array $check) => $check['ok']);
    }
}
