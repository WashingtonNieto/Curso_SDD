<?php

namespace App\Support;

final class Phone
{
    public const PATTERN = '/^\+?\d{6,15}$/';

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        return ($hasPlus ? '+' : '').$digits;
    }

    public static function isValid(?string $phone): bool
    {
        $normalized = self::normalize($phone);

        return $normalized !== null && preg_match(self::PATTERN, $normalized) === 1;
    }
}
