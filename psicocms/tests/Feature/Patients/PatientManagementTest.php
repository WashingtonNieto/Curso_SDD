<?php

namespace Tests\Feature\Patients;

use App\Models\Appointment;
use App\Models\ClinicalEntry;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Lucía',
            'last_name' => 'Pérez García',
            'phone' => ' 600 11 22 33 ',
            'email' => 'LUCIA@EJEMPLO.COM',
            'birth_date' => '1990-05-12',
            'gender' => 'mujer',
            'dni' => '12345678 z',
            'address' => 'Calle Mayor 1',
            'city' => 'Madrid',
            'postal_code' => '28001',
            'occupation' => 'Enfermera',
            'emergency_contact_name' => 'Ana (hermana)',
            'emergency_contact_phone' => '611 22 33 44',
            'preferred_modality' => 'online',
            'status' => 'activo',
            'therapy_type' => 'Cognitivo-conductual',
            'reason' => 'Ansiedad',
            'notes' => '<p>Notas</p><script>alert(1)</script>',
        ], $overrides);
    }

    public function test_pages_load(): void
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->user)->get(route('panel.patients.index'))->assertOk()->assertSee('Gestión de pacientes');
        $this->actingAs($this->user)->get(route('panel.patients.create'))->assertOk()->assertSee('Nuevo paciente');
        $this->actingAs($this->user)->get(route('panel.patients.show', $patient))->assertOk()->assertSee($patient->full_name);
        $this->actingAs($this->user)->get(route('panel.patients.show', [$patient, 'pestana' => 'citas']))->assertOk()->assertSee('Nueva cita para este paciente');
        $this->actingAs($this->user)->get(route('panel.patients.edit', $patient))->assertOk()->assertSee($patient->phone);
    }

    public function test_index_shows_empty_state_without_patients(): void
    {
        $this->actingAs($this->user)
            ->get(route('panel.patients.index'))
            ->assertOk()
            ->assertSee('Aún no tienes pacientes');
    }

    public function test_creates_patient_with_normalized_data_and_sanitized_notes(): void
    {
        $this->actingAs($this->user)
            ->post(route('panel.patients.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('toast');

        $patient = Patient::sole();

        $this->assertSame('600112233', $patient->phone);
        $this->assertSame('611223344', $patient->emergency_contact_phone);
        $this->assertSame('lucia@ejemplo.com', $patient->email);
        $this->assertSame('12345678Z', $patient->dni);
        $this->assertSame('manual', $patient->source);
        $this->assertStringNotContainsString('<script', $patient->notes);
    }

    public function test_rejects_duplicated_phone_with_link_to_existing_patient(): void
    {
        $existing = Patient::factory()->create(['phone' => '600112233', 'first_name' => 'Marta', 'last_name' => 'Ruiz']);

        $this->actingAs($this->user)
            ->from(route('panel.patients.create'))
            ->post(route('panel.patients.store'), $this->payload())
            ->assertRedirect(route('panel.patients.create'))
            ->assertSessionHasErrors('phone')
            ->assertSessionHas('duplicate_patient_id', $existing->id);

        $this->assertSame(1, Patient::count());
    }

    public function test_recovers_deleted_patient_with_same_phone(): void
    {
        $deleted = Patient::factory()->create(['phone' => '600112233']);
        $deleted->delete();

        $this->actingAs($this->user)->post(route('panel.patients.store'), $this->payload())->assertSessionHasNoErrors();

        $deleted->refresh();
        $this->assertFalse($deleted->trashed());
        $this->assertSame('Lucía', $deleted->first_name);
    }

    public function test_requires_phone_and_name(): void
    {
        $this->actingAs($this->user)
            ->post(route('panel.patients.store'), $this->payload(['phone' => 'abc', 'first_name' => '']))
            ->assertSessionHasErrors(['phone', 'first_name']);
    }

    public function test_updates_patient(): void
    {
        $patient = Patient::factory()->create(['phone' => '600112233']);

        $this->actingAs($this->user)
            ->put(route('panel.patients.update', $patient), $this->payload(['first_name' => 'Lucía María', 'status' => 'pausado']))
            ->assertRedirect(route('panel.patients.show', $patient));

        $patient->refresh();
        $this->assertSame('Lucía María', $patient->first_name);
        $this->assertSame('pausado', $patient->status);
    }

    public function test_soft_deletes_patient_by_json_and_keeps_appointments(): void
    {
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $this->actingAs($this->user)
            ->deleteJson(route('panel.patients.destroy', $patient))
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertSoftDeleted($patient);
        $this->assertModelExists($appointment);
    }

    public function test_delete_message_counts_appointments_and_entries(): void
    {
        $patient = Patient::factory()->create();
        Appointment::factory()->count(2)->create(['patient_id' => $patient->id]);
        ClinicalEntry::factory()->create(['patient_id' => $patient->id]);

        $this->actingAs($this->user)
            ->getJson(route('panel.patients.list'))
            ->assertOk()
            ->assertJsonPath('data.0.deleteMessage', fn (string $message) => str_contains($message, '2 citas') && str_contains($message, '1 nota de historia clínica'));
    }

    public function test_list_endpoint_filters_without_reloading(): void
    {
        Patient::factory()->create(['first_name' => 'Lucía', 'last_name' => 'Pérez', 'phone' => '600112233', 'status' => 'activo', 'gender' => 'mujer', 'preferred_modality' => 'online']);
        Patient::factory()->create(['first_name' => 'Carlos', 'last_name' => 'Valdés', 'phone' => '622334455', 'status' => 'pausado', 'gender' => 'hombre', 'preferred_modality' => 'presencial']);

        $names = fn (array $query) => collect($this->actingAs($this->user)->getJson(route('panel.patients.list', $query))->assertOk()->json('data'))->pluck('name')->all();

        $this->assertSame(['Lucía Pérez'], $names(['q' => 'Lucía Pérez']));
        $this->assertSame(['Lucía Pérez'], $names(['q' => '600 11 22']));
        $this->assertSame(['Carlos Valdés'], $names(['estado' => 'pausado']));
        $this->assertSame(['Carlos Valdés'], $names(['genero' => 'hombre']));
        $this->assertSame(['Lucía Pérez'], $names(['modalidad' => 'online']));

        $this->actingAs($this->user)
            ->getJson(route('panel.patients.list', ['q' => 'nadie']))
            ->assertJsonPath('filtered', true)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonStructure(['stats' => ['total', 'activeThisMonth', 'today']]);
    }

    public function test_list_includes_last_and_next_appointment(): void
    {
        $this->freezeTime();
        $patient = Patient::factory()->create();
        Appointment::factory()->create(['patient_id' => $patient->id, 'status' => 'completada', 'starts_at' => now()->subWeek(), 'ends_at' => now()->subWeek()->addMinutes(50)]);
        Appointment::factory()->create(['patient_id' => $patient->id, 'status' => 'confirmada', 'starts_at' => now()->addWeek()->setTime(10, 0), 'ends_at' => now()->addWeek()->setTime(10, 50)]);

        $this->actingAs($this->user)
            ->getJson(route('panel.patients.list'))
            ->assertJsonPath('data.0.nextAppointment.time', '10:00')
            ->assertJsonPath('data.0.lastAppointment', now()->subWeek()->translatedFormat('j M Y'));
    }

    public function test_paginates_list(): void
    {
        Patient::factory()->count(12)->create();

        $this->actingAs($this->user)
            ->getJson(route('panel.patients.list', ['page' => 2]))
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.lastPage', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_autocomplete_search(): void
    {
        Patient::factory()->create(['first_name' => 'Lucía', 'last_name' => 'Pérez', 'phone' => '600112233', 'email' => 'lucia@ejemplo.com']);
        Patient::factory()->count(10)->create(['first_name' => 'Luis']);

        $this->actingAs($this->user)
            ->getJson(route('panel.patients.search', ['q' => 'Lucía']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone', '600112233')
            ->assertJsonPath('data.0.email', 'lucia@ejemplo.com');

        $this->actingAs($this->user)->getJson(route('panel.patients.search', ['q' => 'Lu']))->assertJsonCount(8, 'data');
        $this->actingAs($this->user)->getJson(route('panel.patients.search', ['q' => 'L']))->assertJsonCount(0, 'data');
    }

    public function test_new_appointment_form_is_prefilled_from_patient(): void
    {
        $patient = Patient::factory()->create(['first_name' => 'Lucía', 'phone' => '600112233', 'preferred_modality' => 'online']);

        $this->actingAs($this->user)
            ->get(route('panel.appointments.create', ['paciente' => $patient->id]))
            ->assertOk()
            ->assertSee('value="600112233"', false)
            ->assertSee('value="Lucía"', false);
    }

    public function test_guest_cannot_use_json_endpoints(): void
    {
        $this->getJson(route('panel.patients.list'))->assertUnauthorized();
        $this->getJson(route('panel.patients.search', ['q' => 'Lu']))->assertUnauthorized();
    }
}
