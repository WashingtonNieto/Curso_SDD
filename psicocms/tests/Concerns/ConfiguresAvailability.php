<?php

namespace Tests\Concerns;

use App\Models\AvailabilitySetting;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

trait ConfiguresAvailability
{
    protected function freezeTime(string $datetime = '2026-10-05 07:00:00'): void
    {
        Carbon::setTestNow($datetime);
        CarbonImmutable::setTestNow($datetime);
    }

    protected function configureDefaultAvailability(): void
    {
        AvailabilitySetting::updateOrCreate(['modality' => 'online'], [
            'session_duration' => 50, 'break_enabled' => true, 'break_minutes' => 10,
            'day_start' => '09:00:00', 'day_end' => '14:00:00', 'needs_review' => false,
        ]);

        AvailabilitySetting::updateOrCreate(['modality' => 'presencial'], [
            'session_duration' => 50, 'break_enabled' => false, 'break_minutes' => 10,
            'day_start' => '09:00:00', 'day_end' => '14:00:00', 'needs_review' => false,
        ]);

        $service = app(AvailabilityService::class);
        $service->fillWeekdays('online', [1, 2, 3, 4, 5]);
        $service->fillWeekdays('presencial', [1, 2, 3, 4, 5]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }
}
