<?php

namespace Tests\Feature\Calendar;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\VacationPeriod;
use App\Services\AvailabilityService;
use App\Services\CalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ConfiguresAvailability;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use ConfiguresAvailability, RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeTime();
        $this->configureDefaultAvailability();
        $this->user = User::factory()->create();
    }

    private function appointment(string $start, string $modality = 'online', string $status = 'confirmada', int $break = 10): Appointment
    {
        $startsAt = CarbonImmutable::parse($start);

        return Appointment::factory()->create([
            'patient_id' => Patient::factory()->create(['first_name' => 'Andrea', 'last_name' => 'Torres', 'phone' => fake()->unique()->numerify('6########')])->id,
            'modality' => $modality,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(50),
            'break_minutes' => $break,
            'status' => $status,
        ]);
    }

    public function test_calendar_page_loads_with_its_configuration(): void
    {
        $this->actingAs($this->user)
            ->get(route('panel.calendar'))
            ->assertOk()
            ->assertSee('Agenda del día')
            ->assertSee('vendor/fullcalendar/index.global.min.js', false)
            ->assertSee(route('panel.calendar.events'), false);
    }

    public function test_events_feed_returns_appointments_and_vacations(): void
    {
        $online = $this->appointment('2026-10-06 10:00:00');
        $this->appointment('2026-10-07 09:50:00', 'presencial', 'cancelada', 0);
        VacationPeriod::create(['start_date' => '2026-10-15', 'end_date' => '2026-10-16', 'note' => 'Congreso']);

        $response = $this->actingAs($this->user)
            ->getJson(route('panel.calendar.events', ['start' => '2026-09-28T00:00:00+02:00', 'end' => '2026-11-09T00:00:00+01:00']))
            ->assertOk()
            ->assertJsonCount(3);

        $events = collect($response->json());
        $first = $events->firstWhere('id', (string) $online->id);

        $this->assertSame('Andrea Torres', $first['title']);
        $this->assertSame('2026-10-06T10:00:00', $first['start']);
        $this->assertTrue($first['editable']);
        $this->assertContains('fc-event--online', $first['classNames']);
        $this->assertSame(50, $first['extendedProps']['duration']);
        $this->assertSame(route('panel.appointments.move', $online), $first['extendedProps']['urls']['move']);

        $cancelled = $events->first(fn ($event) => in_array('is-cancelled', $event['classNames'] ?? [], true));
        $this->assertFalse($cancelled['editable']);

        $vacation = $events->firstWhere('display', 'background');
        $this->assertSame('Congreso', $vacation['title']);
        $this->assertSame('2026-10-15', $vacation['start']);
        $this->assertSame('2026-10-17', $vacation['end']);
    }

    public function test_events_feed_only_includes_the_requested_range(): void
    {
        $this->appointment('2026-10-06 10:00:00');
        $this->appointment('2026-12-01 10:00:00');

        $this->actingAs($this->user)
            ->getJson(route('panel.calendar.events', ['start' => '2026-10-05', 'end' => '2026-10-12']))
            ->assertJsonCount(1);

        $this->actingAs($this->user)
            ->getJson(route('panel.calendar.events', ['start' => '2026-10-12', 'end' => '2026-10-05']))
            ->assertStatus(422);
    }

    public function test_appointment_can_be_moved_to_a_free_time_keeping_its_duration(): void
    {
        $appointment = $this->appointment('2026-10-06 10:00:00');

        $this->actingAs($this->user)
            ->patchJson(route('panel.appointments.move', $appointment), ['start' => '2026-10-08 16:30'])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $appointment->refresh();
        $this->assertSame('2026-10-08 16:30', $appointment->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2026-10-08 17:20', $appointment->ends_at->format('Y-m-d H:i'));
    }

    public function test_moving_onto_an_occupied_time_is_rejected(): void
    {
        $this->appointment('2026-10-06 10:00:00');
        $moving = $this->appointment('2026-10-07 10:00:00');

        $this->actingAs($this->user)
            ->patchJson(route('panel.appointments.move', $moving), ['start' => '2026-10-06 10:30'])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Ese horario se solapa con la cita de Andrea Torres a las 10:00.']);

        $this->assertSame('2026-10-07 10:00', $moving->fresh()->starts_at->format('Y-m-d H:i'));
    }

    public function test_moving_to_the_past_or_moving_cancelled_appointments_is_rejected(): void
    {
        $appointment = $this->appointment('2026-10-06 10:00:00');
        $cancelled = $this->appointment('2026-10-07 10:00:00', status: 'cancelada');

        $this->actingAs($this->user)
            ->patchJson(route('panel.appointments.move', $appointment), ['start' => '2026-10-01 10:00'])
            ->assertStatus(422);

        $this->actingAs($this->user)
            ->patchJson(route('panel.appointments.move', $cancelled), ['start' => '2026-10-09 10:00'])
            ->assertStatus(422);

        $this->actingAs($this->user)
            ->patchJson(route('panel.appointments.move', $appointment), ['start' => 'mañana'])
            ->assertStatus(422);
    }

    public function test_status_and_delete_respond_with_json_for_the_calendar(): void
    {
        $appointment = $this->appointment('2026-10-06 10:00:00');

        $this->actingAs($this->user)
            ->patchJson(route('panel.appointments.status', $appointment), ['status' => 'completada'])
            ->assertOk()
            ->assertJson(['status' => 'completada']);

        $this->actingAs($this->user)
            ->deleteJson(route('panel.appointments.destroy', $appointment))
            ->assertOk()
            ->assertJson(['message' => 'Cita eliminada.']);

        $this->assertModelMissing($appointment);
    }

    public function test_business_hours_come_from_the_weekly_grid(): void
    {
        app(AvailabilityService::class)->saveWeeklySlots('online', [
            ['weekday' => 7, 'time' => '09:00'],
            ['weekday' => 7, 'time' => '10:00'],
        ]);

        $hours = collect(app(CalendarService::class)->businessHours());

        $this->assertContains(['daysOfWeek' => [0], 'startTime' => '09:00', 'endTime' => '10:50'], $hours->all());
        $this->assertTrue($hours->contains(fn ($range) => $range['daysOfWeek'] === [1] && $range['startTime'] === '09:00'));
    }
}
