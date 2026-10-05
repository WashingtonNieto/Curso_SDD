<?php

namespace Tests\Feature\Appointments;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\User;
use App\Models\VacationPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ConfiguresAvailability;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use ConfiguresAvailability, RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeTime();
        $this->configureDefaultAvailability();
        $this->user = User::factory()->create();

        Plan::create(['name' => 'Sesión online', 'modality' => 'online', 'price' => 150000, 'is_active' => true]);
        Plan::create(['name' => 'Sesión presencial', 'modality' => 'presencial', 'price' => 180000, 'is_active' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Lucía',
            'last_name' => 'Pérez García',
            'phone' => '600112233',
            'email' => '',
            'modality' => 'online',
            'date' => '2026-10-06',
            'custom_time' => '0',
            'slot_time' => '10:00',
            'custom_time_value' => '',
            'status' => 'confirmada',
            'source' => 'telefono',
            'price' => '',
            'reason' => 'Ansiedad',
            'internal_notes' => '',
        ], $overrides);
    }

    private function existingAppointment(string $modality, string $start, int $break = 10): Appointment
    {
        $startsAt = CarbonImmutable::parse($start);

        return Appointment::factory()->create([
            'patient_id' => Patient::factory()->create(['first_name' => 'Marta', 'last_name' => 'Ruiz'])->id,
            'modality' => $modality,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(50),
            'break_minutes' => $break,
            'status' => 'confirmada',
        ]);
    }

    public function test_pages_load(): void
    {
        $this->actingAs($this->user)->get(route('panel.appointments.index'))->assertOk()->assertSee('Aún no tienes citas');
        $this->actingAs($this->user)->get(route('panel.appointments.create'))->assertOk()->assertSee('Huecos libres');
    }

    public function test_creating_an_appointment_creates_the_patient_and_snapshots_the_schedule(): void
    {
        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload())
            ->assertRedirect(route('panel.appointments.index'))
            ->assertSessionHasNoErrors();

        $patient = Patient::sole();
        $appointment = Appointment::sole();

        $this->assertSame('600112233', $patient->phone);
        $this->assertSame('manual', $patient->source);
        $this->assertTrue($appointment->patient->is($patient));
        $this->assertSame('2026-10-06 10:00', $appointment->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2026-10-06 10:50', $appointment->ends_at->format('Y-m-d H:i'));
        $this->assertSame(10, $appointment->break_minutes);
        $this->assertSame('150000.00', $appointment->price);
    }

    public function test_existing_phone_with_spaces_links_the_same_patient(): void
    {
        $patient = Patient::factory()->create(['phone' => '600112233', 'first_name' => 'Lucía', 'email' => null]);

        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload(['phone' => ' 600 11 22 33 ', 'email' => 'lucia@example.com']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Patient::count());
        $this->assertTrue(Appointment::sole()->patient->is($patient));
        $this->assertSame('lucia@example.com', $patient->fresh()->email);
    }

    public function test_overlapping_custom_time_is_rejected_with_a_clear_message(): void
    {
        $this->existingAppointment('online', '2026-10-06 10:00:00');

        $response = $this->actingAs($this->user)
            ->from(route('panel.appointments.create'))
            ->post(route('panel.appointments.store'), $this->payload([
                'modality' => 'presencial',
                'custom_time' => '1',
                'custom_time_value' => '10:30',
            ]));

        $response->assertRedirect(route('panel.appointments.create'))->assertSessionHasErrors('slot_time');
        $this->assertStringContainsString('se solapa con la cita de Marta Ruiz a las 10:00', session('errors')->first('slot_time'));
        $this->assertSame(1, Appointment::count());
    }

    public function test_taken_slot_is_rejected(): void
    {
        $this->existingAppointment('presencial', '2026-10-06 09:50:00', 0);

        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload(['slot_time' => '10:00']))
            ->assertSessionHasErrors('slot_time');

        $this->assertSame(1, Appointment::count());
    }

    public function test_slot_inside_vacation_period_is_rejected_but_custom_time_is_allowed(): void
    {
        VacationPeriod::create(['start_date' => '2026-10-06', 'end_date' => '2026-10-06']);

        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload())
            ->assertSessionHasErrors('slot_time');

        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload(['custom_time' => '1', 'custom_time_value' => '10:00']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Appointment::count());
    }

    public function test_custom_time_outside_the_weekly_grid_is_allowed_when_free(): void
    {
        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload(['custom_time' => '1', 'custom_time_value' => '18:30', 'price' => '130000']))
            ->assertSessionHasNoErrors();

        $appointment = Appointment::sole();
        $this->assertSame('18:30', $appointment->starts_at->format('H:i'));
        $this->assertSame('130000.00', $appointment->price);
    }

    public function test_slot_is_required_unless_custom_time(): void
    {
        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload(['slot_time' => '']))
            ->assertSessionHasErrors('slot_time');

        $this->actingAs($this->user)
            ->post(route('panel.appointments.store'), $this->payload(['custom_time' => '1', 'custom_time_value' => '']))
            ->assertSessionHasErrors('custom_time_value');
    }

    public function test_updating_keeps_its_own_slot_and_can_move_to_a_free_one(): void
    {
        $this->actingAs($this->user)->post(route('panel.appointments.store'), $this->payload());
        $appointment = Appointment::sole();

        $this->actingAs($this->user)
            ->put(route('panel.appointments.update', $appointment), $this->payload(['internal_notes' => 'Trae informe']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Trae informe', $appointment->fresh()->internal_notes);
        $this->assertSame('10:00', $appointment->fresh()->starts_at->format('H:i'));

        $this->actingAs($this->user)
            ->put(route('panel.appointments.update', $appointment), $this->payload(['slot_time' => '12:00', 'internal_notes' => 'Trae informe']))
            ->assertSessionHasNoErrors();

        $appointment->refresh();
        $this->assertSame('12:00', $appointment->starts_at->format('H:i'));
        $this->assertSame(1, Appointment::count());
    }

    public function test_reactivating_a_cancelled_appointment_checks_overlaps(): void
    {
        $cancelled = $this->existingAppointment('online', '2026-10-06 10:00:00');
        $cancelled->update(['status' => 'cancelada']);
        $this->existingAppointment('online', '2026-10-06 10:00:00');

        $this->actingAs($this->user)
            ->from(route('panel.appointments.index'))
            ->patch(route('panel.appointments.status', $cancelled), ['status' => 'confirmada'])
            ->assertSessionHasErrors('slot_time');

        $this->assertSame('cancelada', $cancelled->fresh()->status);

        $this->actingAs($this->user)
            ->patch(route('panel.appointments.status', $cancelled), ['status' => 'no_asistio']);

        $this->assertSame('cancelada', $cancelled->fresh()->status);
    }

    public function test_status_can_be_changed_quickly(): void
    {
        $appointment = $this->existingAppointment('online', '2026-10-06 10:00:00');

        $this->actingAs($this->user)
            ->from(route('panel.appointments.index'))
            ->patch(route('panel.appointments.status', $appointment), ['status' => 'completada'])
            ->assertRedirect(route('panel.appointments.index'));

        $this->assertSame('completada', $appointment->fresh()->status);
    }

    public function test_appointment_can_be_deleted(): void
    {
        $appointment = $this->existingAppointment('online', '2026-10-06 10:00:00');

        $this->actingAs($this->user)
            ->delete(route('panel.appointments.destroy', $appointment))
            ->assertRedirect(route('panel.appointments.index'));

        $this->assertModelMissing($appointment);
    }

    public function test_index_filters_by_phone_written_with_spaces_and_by_status(): void
    {
        $this->existingAppointment('online', '2026-10-06 10:00:00');
        $other = Appointment::factory()->create([
            'patient_id' => Patient::factory()->create(['first_name' => 'Pablo', 'phone' => '611223344'])->id,
            'starts_at' => '2026-10-07 09:00:00',
            'ends_at' => '2026-10-07 09:50:00',
            'status' => 'pendiente',
        ]);

        $this->actingAs($this->user)
            ->get(route('panel.appointments.index', ['q' => '611 22 33 44']))
            ->assertOk()
            ->assertSee('Pablo')
            ->assertDontSee('Marta Ruiz');

        $this->actingAs($this->user)
            ->get(route('panel.appointments.index', ['estado' => 'pendiente']))
            ->assertSee($other->patient->full_name)
            ->assertDontSee('Marta Ruiz');

        $this->actingAs($this->user)
            ->get(route('panel.appointments.index', ['q' => 'nadie']))
            ->assertSee('No hay citas con estos filtros');
    }

    public function test_slots_endpoint_returns_free_slots_and_block_reasons(): void
    {
        $this->existingAppointment('online', '2026-10-06 10:00:00');

        $this->actingAs($this->user)
            ->getJson(route('panel.availability.slots', ['modalidad' => 'online', 'fecha' => '2026-10-06']))
            ->assertOk()
            ->assertJson(['slots' => ['09:00', '11:00', '12:00', '13:00'], 'blocked' => null]);

        VacationPeriod::create(['start_date' => '2026-10-08', 'end_date' => '2026-10-09']);

        $this->actingAs($this->user)
            ->getJson(route('panel.availability.slots', ['modalidad' => 'online', 'fecha' => '2026-10-08']))
            ->assertJson(['slots' => [], 'blocked' => 'vacation_period']);

        $this->actingAs($this->user)
            ->getJson(route('panel.availability.slots', ['modalidad' => 'otra', 'fecha' => 'mañana']))
            ->assertStatus(422);
    }
}
