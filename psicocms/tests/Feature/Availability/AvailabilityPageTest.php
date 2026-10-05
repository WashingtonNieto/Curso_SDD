<?php

namespace Tests\Feature\Availability;

use App\Models\Appointment;
use App\Models\AvailabilitySetting;
use App\Models\AvailabilitySlot;
use App\Models\User;
use App\Models\VacationPeriod;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ConfiguresAvailability;
use Tests\TestCase;

class AvailabilityPageTest extends TestCase
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

    public function test_page_shows_both_modalities_and_vacation_panels(): void
    {
        $this->actingAs($this->user)
            ->get(route('panel.availability'))
            ->assertOk()
            ->assertSee('Modo vacaciones')
            ->assertSee('Periodos de vacaciones')
            ->assertSee('Horario semanal · Online')
            ->assertSee('Horario semanal · Presencial')
            ->assertSee('data-week-grid', false);
    }

    public function test_changing_the_schedule_prunes_slots_and_shows_review_banner(): void
    {
        $this->actingAs($this->user)
            ->put(route('panel.availability.schedule', 'online'), ['online' => [
                'session_duration' => 45,
                'break_enabled' => '0',
                'break_minutes' => 10,
                'day_start' => '09:00',
                'day_end' => '14:00',
            ]])
            ->assertRedirect(route('panel.availability', ['tab' => 'online']));

        $setting = AvailabilitySetting::forModality('online');
        $this->assertSame(45, $setting->session_duration);
        $this->assertFalse($setting->break_enabled);
        $this->assertTrue($setting->needs_review);
        $this->assertTrue(AvailabilitySetting::forModality('presencial')->needs_review);
        $this->assertSame(0, AvailabilitySlot::modality('online')->where('start_time', '10:00:00')->count());

        $this->actingAs($this->user)
            ->get(route('panel.availability'))
            ->assertSee('Revisa y vuelve a marcar tus huecos semanales');
    }

    public function test_schedule_validation_rejects_impossible_hours(): void
    {
        $this->actingAs($this->user)
            ->from(route('panel.availability'))
            ->put(route('panel.availability.schedule', 'presencial'), ['presencial' => [
                'session_duration' => 120,
                'break_enabled' => '0',
                'day_start' => '09:00',
                'day_end' => '10:00',
            ]])
            ->assertSessionHasErrors('presencial.day_end');

        $this->actingAs($this->user)
            ->put(route('panel.availability.schedule', 'presencial'), ['presencial' => [
                'session_duration' => 50,
                'day_start' => '14:00',
                'day_end' => '09:00',
            ]])
            ->assertSessionHasErrors('presencial.day_end');
    }

    public function test_weekly_slots_are_saved_as_json(): void
    {
        AvailabilitySetting::query()->update(['needs_review' => true]);

        $this->actingAs($this->user)
            ->putJson(route('panel.availability.weekly', 'presencial'), ['slots' => [
                ['weekday' => 2, 'time' => '09:50'],
                ['weekday' => 2, 'time' => '10:40'],
                ['weekday' => 6, 'time' => '09:00'],
            ]])
            ->assertOk()
            ->assertJson(['saved' => 3, 'needs_review' => ['online']]);

        $this->assertSame(3, AvailabilitySlot::modality('presencial')->count());

        $this->actingAs($this->user)
            ->putJson(route('panel.availability.weekly', 'presencial'), ['slots' => []])
            ->assertOk()
            ->assertJson(['saved' => 0]);

        $this->actingAs($this->user)
            ->putJson(route('panel.availability.weekly', 'presencial'), ['slots' => [['weekday' => 9, 'time' => 'mañana']]])
            ->assertStatus(422);
    }

    public function test_vacation_mode_toggles_via_ajax(): void
    {
        $this->actingAs($this->user)
            ->patchJson(route('panel.availability.vacation-mode'), ['enabled' => true])
            ->assertOk()
            ->assertJson(['enabled' => true]);

        $this->assertTrue(app(AvailabilityService::class)->isVacationMode());

        $this->actingAs($this->user)
            ->patchJson(route('panel.availability.vacation-mode'), ['enabled' => false])
            ->assertJson(['enabled' => false]);

        $this->assertFalse(app(AvailabilityService::class)->isVacationMode());
    }

    public function test_vacation_periods_can_be_added_and_deleted(): void
    {
        $this->actingAs($this->user)
            ->post(route('panel.availability.vacations.store'), ['start_date' => '2026-12-22', 'end_date' => '2027-01-06', 'note' => 'Navidad'])
            ->assertRedirect(route('panel.availability'))
            ->assertSessionHasNoErrors();

        $period = VacationPeriod::sole();
        $this->assertSame('Navidad', $period->note);

        $this->actingAs($this->user)
            ->delete(route('panel.availability.vacations.destroy', $period))
            ->assertRedirect(route('panel.availability'));

        $this->assertModelMissing($period);
    }

    public function test_vacation_periods_cannot_overlap_or_end_before_starting(): void
    {
        VacationPeriod::create(['start_date' => '2026-08-01', 'end_date' => '2026-08-15']);

        $this->actingAs($this->user)
            ->post(route('panel.availability.vacations.store'), ['start_date' => '2026-08-10', 'end_date' => '2026-08-20'])
            ->assertSessionHasErrors('start_date');

        $this->actingAs($this->user)
            ->post(route('panel.availability.vacations.store'), ['start_date' => '2026-09-10', 'end_date' => '2026-09-01'])
            ->assertSessionHasErrors('end_date');

        $this->assertSame(1, VacationPeriod::count());
    }

    public function test_adding_a_period_with_appointments_warns_without_blocking(): void
    {
        Appointment::factory()->create(['starts_at' => '2026-10-20 10:00:00', 'ends_at' => '2026-10-20 10:50:00']);

        $this->actingAs($this->user)
            ->post(route('panel.availability.vacations.store'), ['start_date' => '2026-10-19', 'end_date' => '2026-10-23'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('toast', fn ($toast) => str_contains($toast['message'], '1 cita'));

        $this->assertSame(1, VacationPeriod::count());
    }
}
