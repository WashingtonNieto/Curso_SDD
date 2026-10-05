<?php

use App\Models\Phrase;
use App\Models\SiteImage;
use App\Services\SettingsService;
use App\Services\ThemeManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('phrase')) {
    function phrase(string $key, ?string $default = null): string
    {
        $custom = rescue(
            fn () => Cache::rememberForever('psicocms.phrases', fn () => Phrase::query()->pluck('value', 'key')->all()),
            [],
            false
        );

        return $custom[$key] ?? config('phrases.'.$key.'.default') ?? $default ?? $key;
    }
}

if (! function_exists('site_image')) {
    function site_image(string $key): ?string
    {
        $images = rescue(
            fn () => Cache::rememberForever('psicocms.site_images', fn () => SiteImage::query()->pluck('path', 'key')->all()),
            [],
            false
        );

        return isset($images[$key]) ? Storage::disk('public')->url($images[$key]) : null;
    }
}

if (! function_exists('theme_asset')) {
    function theme_asset(string $path, ?string $theme = null): string
    {
        $manager = app(ThemeManager::class);
        $slug = $theme ?? $manager->activeSlug();
        $version = $manager->find($slug)['version'] ?? '1.0.0';

        return url('themes/'.$slug.'/'.ltrim($path, '/')).'?v='.$version;
    }
}

if (! function_exists('theme_image')) {
    function theme_image(string $key, ?string $theme = null): ?string
    {
        if ($uploaded = site_image($key)) {
            return $uploaded;
        }

        $manager = app(ThemeManager::class);
        $default = $manager->find($theme ?? $manager->activeSlug())['images'][$key] ?? null;

        return $default ? theme_asset($default, $theme) : null;
    }
}

if (! function_exists('money')) {
    function money(int|float|string|null $amount, bool $withCode = false): string
    {
        $currency = config('psicocms.currency');
        $formatted = $currency['symbol'].' '.number_format(
            (float) $amount,
            $currency['decimals'],
            $currency['decimal_separator'],
            $currency['thousands_separator']
        );

        return $withCode ? $formatted.' '.$currency['code'] : $formatted;
    }
}

if (! function_exists('public_storage_url')) {
    function public_storage_url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
