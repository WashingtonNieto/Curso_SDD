<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PanelRoutesProtectionTest extends TestCase
{
    public function test_every_panel_route_requires_authentication(): void
    {
        $panelRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'panel-psicologa'));

        $this->assertNotEmpty($panelRoutes);

        foreach ($panelRoutes as $route) {
            $this->assertContains('auth', $route->gatherMiddleware(), "La ruta /{$route->uri()} no está protegida con el middleware auth.");
        }
    }
}
