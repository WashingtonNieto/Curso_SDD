<?php

namespace Tests\Feature\Availability;

use App\Models\Appointment;
use App\Models\AvailabilitySetting;
use App\Models\AvailabilitySlot;
use App\Models\VacationPeriod;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ConfiguresAvailability;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use ConfiguresAvailability, RefreshDatabase;

    private AvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeTime();
        $this->configureDefaultAvailability();
        $this->service = app(AvailabilityService::class);
    }

    private function appointment(string $modality, string $start, int $duration = 50, int $break = 10, string $status = 'confirmada'): Appointment
    {
        $startsAt = CarbonImmutable::parse($start);

        return Appointment::factory()->create([
            'modality' => $modality,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes($duration),
            'break_minutes' => $break,
            'status' => $status,
        ]);
    }

    public function test_slots_follow_the_grid_of_each_modality(): void
    {
        $this->assertSame(['09:00', '10:00', '11:00', '12:00', '13:00'], $this->service->slotsForDate('online', '2026-10-06'));
        $this->assertSame(['09:00', '09:50', '10:40', '11:30', '12:20', '13:10'], $this->service->slotsForDate('presencial', '2026-10-06'));
    }

    public function test_days_without_marked_slots_have_no_availability(): void
    {
        $this->assertSame([], $this->service->slotsForDate('online', '2026-10-10'));
    }

    public function test_vacation_mode_blocks_every_slot(): void
    {
        $this->service->setVacationMode(true);

        $this->assertSame('vacation_mode', $this->service->blockReason('2026-10-06'));
        $this->assertSame([], $this->service->slotsForDate('online', '2026-10-06'));
        $this->assertSame([], $this->service->slotsForDate('online', '2026-10-06', null, true));
    }

    public function test_vacation_periods_block_their_days_in_public_and_panel(): void
    {
        VacationPeriod::create(['start_date' => '2026-10-06', 'end_date' => '2026-10-07']);

        $this->assertSame('vacation_period', $this->service->blockReason('2026-10-07'));
        $this->assertSame([], $this->service->slotsForDate('online', '2026-10-06'));
        $this->assertSame([], $this->service->slotsForDate('presencial', '2026-10-07', null, true));
        $this->assertNotEmpty($this->service->slotsForDate('online', '2026-10-08'));
    }

    public function test_appointments_block_overlapping_slots_of_any_modality(): void
    {
        $this->appointment('online', '2026-10-06 10:00:00');

        $this->assertSame(['09:00', '11:00', '12:00', '13:00'], $this->service->slotsForDate('online', '2026-10-06'));
        $this->assertSame(['09:00', '11:30', '12:20', '13:10'], $this->service->slotsForDate('presencial', '2026-10-06'));
    }

    public function test_cancelled_appointments_do_not_block(): void
    {
        $this->appointment('online', '2026-10-06 10:00:00', status: 'cancelada');

        $this->assertContains('10:00', $this->service->slotsForDate('online', '2026-10-06'));
    }

    public function test_ignored_appointment_frees_its_own_slot(): void
    {
        $appointment = $this->appointment('online', '2026-10-06 10:00:00');

        $this->assertContains('10:00', $this->service->slotsForDate('online', '2026-10-06', $appointment->id));
    }

    public function test_public_booking_requires_minimum_notice_but_panel_does_not(): void
    {
        $this->freezeTime('2026-10-05 07:30:00');

        $this->assertNotContains('09:00', $this->service->slotsForDate('online', '2026-10-05'));
        $this->assertContains('09:00', $this->service->slotsForDate('online', '2026-10-05', null, true));
    }

    public function test_past_slots_are_never_available(): void
    {
        $this->freezeTime('2026-10-05 11:15:00');

        $this->assertSame(['12:00', '13:00'], $this->service->slotsForDate('online', '2026-10-05', null, true));
    }

    public function test_public_booking_respects_the_horizon(): void
    {
        config(['psicocms.booking.max_days_ahead' => 10]);

        $this->assertSame([], $this->service->slotsForDate('online', '2026-10-20'));
        $this->assertNotEmpty($this->service->slotsForDate('online', '2026-10-20', null, true));
    }

    public function test_overlaps_returns_the_conflicting_appointment(): void
    {
        $existing = $this->appointment('presencial', '2026-10-06 09:50:00', break: 0);

        $conflict = $this->service->overlaps(CarbonImmutable::parse('2026-10-06 10:00'), CarbonImmutable::parse('2026-10-06 11:00'));

        $this->assertTrue($conflict->is($existing));
        $this->assertNull($this->service->overlaps(CarbonImmutable::parse('2026-10-06 10:40'), CarbonImmutable::parse('2026-10-06 11:30')));
    }

    public function test_changing_the_schedule_prunes_slots_and_flags_review(): void
    {
        $changed = $this->service->updateSchedule('online', [
            'session_duration' => 50,
            'break_enabled' => false,
            'break_minutes' => 10,
            'day_start' => '09:00',
            'day_end' => '14:00',
        ]);

        $this->assertTrue($changed);
        $this->assertSame(['online', 'presencial'], $this->service->modalitiesNeedingReview());
        $this->assertSame(0, AvailabilitySlot::modality('online')->where('start_time', '10:00:00')->count());
        $this->assertSame(5, AvailabilitySlot::modality('online')->where('start_time', '09:00:00')->count());
    }

    public function test_saving_unchanged_schedule_does_not_flag_review(): void
    {
        $changed = $this->service->updateSchedule('presencial', [
            'session_duration' => 50,
            'break_enabled' => false,
            'break_minutes' => 10,
            'day_start' => '09:00',
            'day_end' => '14:00',
        ]);

        $this->assertFalse($changed);
        $this->assertSame([], $this->service->modalitiesNeedingReview());
    }

    public function test_saving_weekly_slots_ignores_times_outside_the_grid_and_clears_review(): void
    {
        AvailabilitySetting::query()->update(['needs_review' => true]);

        $saved = $this->service->saveWeeklySlots('online', [
            ['weekday' => 1, 'time' => '09:00'],
            ['weekday' => 1, 'time' => '09:30'],
            ['weekday' => 8, 'time' => '10:00'],
            ['weekday' => 3, 'time' => '13:00'],
        ]);

        $this->assertSame(2, $saved);
        $this->assertSame(['presencial'], $this->service->modalitiesNeedingReview());
        $this->assertSame(['09:00'], $this->service->weeklySlots('online')[1]);
    }

    public function test_weekly_ranges_group_consecutive_slots(): void
    {
        $this->service->saveWeeklySlots('online', [
            ['weekday' => 1, 'time' => '09:00'],
            ['weekday' => 1, 'time' => '10:00'],
            ['weekday' => 1, 'time' => '12:00'],
        ]);

        $this->assertSame([['09:00', '10:50'], ['12:00', '12:50']], $this->service->weeklyRanges('online')[1]);
        $this->assertSame([], $this->service->weeklyRanges('online')[2]);
    }
}
