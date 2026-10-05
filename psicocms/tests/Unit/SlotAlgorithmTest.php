<?php

namespace Tests\Unit;

use App\Services\AvailabilityService;
use PHPUnit\Framework\TestCase;

class SlotAlgorithmTest extends TestCase
{
    public function test_fifty_minutes_with_ten_minutes_break_starts_every_hour(): void
    {
        $this->assertSame(
            ['09:00', '10:00', '11:00', '12:00', '13:00'],
            AvailabilityService::times(50, 10, '09:00', '14:00')
        );
    }

    public function test_fifty_minutes_without_break_starts_every_fifty_minutes(): void
    {
        $this->assertSame(
            ['09:00', '09:50', '10:40', '11:30', '12:20', '13:10'],
            AvailabilityService::times(50, 0, '09:00', '14:00')
        );
    }

    public function test_last_session_must_end_before_closing_time(): void
    {
        $this->assertSame(['09:00', '10:00'], AvailabilityService::times(60, 0, '09:00', '11:30'));
        $this->assertSame(['09:00'], AvailabilityService::times(45, 15, '09:00', '10:30'));
    }

    public function test_no_slots_when_session_does_not_fit(): void
    {
        $this->assertSame([], AvailabilityService::times(90, 0, '09:00', '10:00'));
        $this->assertSame([], AvailabilityService::times(0, 0, '09:00', '14:00'));
    }

    public function test_accepts_times_with_seconds(): void
    {
        $this->assertSame(['16:00', '17:00'], AvailabilityService::times(50, 10, '16:00:00', '18:00:00'));
    }
}
