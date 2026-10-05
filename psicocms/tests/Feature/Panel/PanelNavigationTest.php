<?php

namespace Tests\Feature\Panel;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function menuRoutes(): array
    {
        $routes = ['panel.appointments.create', 'panel.help', 'panel.search'];

        foreach (config('panel.menu') as $item) {
            foreach ($item['children'] ?? [$item] as $entry) {
                $routes[] = $entry['route'];
            }
        }

        return array_unique($routes);
    }

    public function test_every_menu_entry_loads_for_the_psychologist(): void
    {
        $user = User::factory()->create(['first_name' => 'Laura']);

        foreach ($this->menuRoutes() as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('PsicoCMS')
                ->assertSee('Cerrar sesión');
        }
    }

    public function test_home_shows_greeting_and_layout(): void
    {
        $user = User::factory()->create(['first_name' => 'Laura']);

        $this->actingAs($user)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('¡Hola, Laura!')
            ->assertSee('Nueva cita')
            ->assertSee('Ver mi web');
    }

    public function test_active_group_is_expanded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('panel.site.themes'))
            ->assertOk()
            ->assertSee('aria-controls="submenu-mi-web"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_profile_shortcut_redirects_to_general_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/panel-psicologa/perfil')
            ->assertRedirect('/panel-psicologa/configuracion/general');
    }

    public function test_guest_cannot_open_panel_sections(): void
    {
        $this->get(route('panel.patients.index'))->assertRedirect(route('login'));
    }
}
