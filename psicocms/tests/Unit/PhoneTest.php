<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public static function phones(): array
    {
        return [
            'espacios alrededor y entre números' => ['  600 11 22 33  ', '600112233'],
            'guiones' => ['600-11-22-33', '600112233'],
            'puntos' => ['600.11.22.33', '600112233'],
            'paréntesis y prefijo' => ['+34 (600) 11 22 33', '+34600112233'],
            'ya normalizado' => ['600112233', '600112233'],
            'tabulaciones' => ["\t600\t112233\n", '600112233'],
        ];
    }

    #[DataProvider('phones')]
    public function test_normalize(string $input, string $expected): void
    {
        $this->assertSame($expected, Phone::normalize($input));
    }

    public function test_normalize_returns_null_for_empty_values(): void
    {
        $this->assertNull(Phone::normalize(null));
        $this->assertNull(Phone::normalize('   '));
        $this->assertNull(Phone::normalize('abc'));
    }

    public function test_plus_only_kept_when_leading(): void
    {
        $this->assertSame('34600112233', Phone::normalize('34+600112233'));
    }

    public function test_is_valid(): void
    {
        $this->assertTrue(Phone::isValid('600 11 22 33'));
        $this->assertTrue(Phone::isValid('+34 600 11 22 33'));
        $this->assertFalse(Phone::isValid('12345'));
        $this->assertFalse(Phone::isValid('1234567890123456'));
        $this->assertFalse(Phone::isValid(''));
    }
}
