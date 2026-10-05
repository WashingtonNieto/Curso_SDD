<?php

namespace Tests\Feature\Panel;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ConfiguresAvailability;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use ConfiguresAvailability, RefreshDatabase;

    public function test_dashboard_shows_empty_states_without_data(): void
    {
        $this->freezeTime();

        $this->actingAs(User::factory()->create(['first_name' => 'Laura']))
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('¡Hola, Laura!')
            ->assertSee('Hoy no tienes citas')
            ->assertSee('Sin reservas nuevas')
            ->assertSee('No disponible');
    }

    public function test_dashboard_shows_real_statistics(): void
    {
        $this->freezeTime('2026-10-05 07:00:00');
        $this->configureDefaultAvailability();

        $patient = Patient::factory()->create(['first_name' => 'Elena', 'last_name' => 'Martínez', 'status' => 'activo']);

        Appointment::factory()->create(['patient_id' => $patient->id, 'starts_at' => '2026-10-05 10:00:00', 'ends_at' => '2026-10-05 10:50:00', 'price' => 50000, 'modality' => 'online', 'source' => 'web', 'seen_at' => null]);
        Appointment::factory()->create(['patient_id' => $patient->id, 'starts_at' => '2026-10-02 10:00:00', 'ends_at' => '2026-10-02 10:50:00', 'price' => 60000]);
        Appointment::factory()->create(['patient_id' => $patient->id, 'starts_at' => '2026-10-05 12:00:00', 'ends_at' => '2026-10-05 12:50:00', 'price' => 70000, 'status' => 'cancelada']);

        $this->actingAs(User::factory()->create())
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Elena Martínez')
            ->assertSee('$ 110.000')
            ->assertSee('09:00 – 13:50')
            ->assertDontSee('Hoy no tienes citas')
            ->assertDontSee('Sin reservas nuevas');
    }
}
