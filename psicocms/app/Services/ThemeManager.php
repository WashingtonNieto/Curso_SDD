<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ThemeManager
{
    private ?array $themes = null;

    public function __construct(private readonly SettingsService $settings) {}

    public function path(string $slug = ''): string
    {
        return base_path('themes'.($slug !== '' ? DIRECTORY_SEPARATOR.$slug : ''));
    }

    public function all(): array
    {
        if ($this->themes !== null) {
            return $this->themes;
        }

        $themes = [];

        foreach (File::directories($this->path()) as $directory) {
            $manifest = $this->readManifest($directory);

            if ($manifest !== null) {
                $themes[$manifest['slug']] = $manifest;
            }
        }

        uasort($themes, fn (array $a, array $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        return $this->themes = $themes;
    }

    public function find(?string $slug): ?array
    {
        return $slug === null ? null : ($this->all()[$slug] ?? null);
    }

    public function exists(?string $slug): bool
    {
        return $this->find($slug) !== null;
    }

    public function activeSlug(): string
    {
        $slug = $this->settings->get('theme.active', config('psicocms.default_theme'));

        return $this->exists($slug) ? $slug : (array_key_first($this->all()) ?? config('psicocms.default_theme'));
    }

    public function activeMode(): string
    {
        $mode = $this->settings->get('theme.mode', 'landing');

        return array_key_exists($mode, config('psicocms.theme_modes')) ? $mode : 'landing';
    }

    private function readManifest(string $directory): ?array
    {
        $file = $directory.DIRECTORY_SEPARATOR.'theme.json';

        if (! is_file($file)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($file), true);
        $slug = basename($directory);

        if (! is_array($data) || ($data['slug'] ?? null) !== $slug || empty($data['name'])) {
            return null;
        }

        return $data + ['version' => '1.0.0', 'preview' => [], 'images' => []];
    }
}
