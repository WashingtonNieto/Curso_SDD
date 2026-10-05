<?php

namespace Tests\Feature;

use Tests\TestCase;

class MoneyFormatTest extends TestCase
{
    public function test_amounts_are_formatted_as_colombian_pesos(): void
    {
        $this->assertSame('$ 150.000', money(150000));
        $this->assertSame('$ 1.250.000', money('1250000.00'));
        $this->assertSame('$ 0', money(null));
        $this->assertSame('$ 180.000 COP', money(180000, true));
    }
}
