<?php

namespace App\Support;

final class Installation
{
    public static function lockPath(): string
    {
        return config('psicocms.installed_lock', storage_path('app/installed.lock'));
    }

    public static function isInstalled(): bool
    {
        return is_file(self::lockPath());
    }

    public static function markInstalled(): void
    {
        file_put_contents(self::lockPath(), now()->toIso8601String(), LOCK_EX);
    }
}
