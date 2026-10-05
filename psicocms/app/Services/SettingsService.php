<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class SettingsService
{
    private const CACHE_KEY = 'psicocms.settings';

    private ?array $loaded = null;

    public function all(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        try {
            return $this->loaded = Cache::rememberForever(
                self::CACHE_KEY,
                fn () => Setting::query()->pluck('value', 'key')->all()
            );
        } catch (Throwable) {
            return [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (! array_key_exists($key, $all) || $all[$key] === null) {
            return $default;
        }

        return $this->decode($key, $all[$key]) ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function group(string $prefix): array
    {
        $result = [];

        foreach (array_keys($this->all()) as $key) {
            if (str_starts_with($key, $prefix.'.')) {
                $result[substr($key, strlen($prefix) + 1)] = $this->get($key);
            }
        }

        return $result;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $this->encode($key, $value)]);
        $this->flush();
    }

    public function many(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $this->encode($key, $value)]);
        }

        $this->flush();
    }

    public function setIfMissing(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $this->encode($key, $value)]);
        }

        $this->flush();
    }

    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();
        $this->flush();
    }

    public function flush(): void
    {
        $this->loaded = null;
        Cache::forget(self::CACHE_KEY);
    }

    private function encode(string $key, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE);

        return $this->isEncrypted($key) ? Crypt::encryptString($json) : $json;
    }

    private function decode(string $key, string $raw): mixed
    {
        try {
            $json = $this->isEncrypted($key) ? Crypt::decryptString($raw) : $raw;
        } catch (Throwable) {
            return null;
        }

        return json_decode($json, true);
    }

    private function isEncrypted(string $key): bool
    {
        return in_array($key, config('psicocms.encrypted_settings', []), true);
    }
}
