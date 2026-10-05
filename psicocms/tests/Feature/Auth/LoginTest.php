<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'laura@example.com',
            'phone' => '600112233',
            'password' => 'secreto123',
        ]);
    }

    private function credentials(array $overrides = []): array
    {
        return array_merge([
            'email' => 'laura@example.com',
            'phone' => '600112233',
            'password' => 'secreto123',
        ], $overrides);
    }

    public function test_login_page_is_displayed(): void
    {
        $this->get('/acceso-psicologa')
            ->assertOk()
            ->assertSee('Accede a tu panel')
            ->assertSee('Mantener la sesión iniciada');
    }

    public function test_user_can_login_with_email_phone_and_password(): void
    {
        $this->post('/acceso-psicologa', $this->credentials([
            'email' => '  Laura@Example.com ',
            'phone' => ' 600 11 22 33 ',
        ]))->assertRedirect(route('panel.home'));

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_fails_with_wrong_phone(): void
    {
        $this->from('/acceso-psicologa')
            ->post('/acceso-psicologa', $this->credentials(['phone' => '699999999']))
            ->assertRedirect('/acceso-psicologa')
            ->assertSessionHasErrors(['login' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->post('/acceso-psicologa', $this->credentials(['password' => 'incorrecta1']))
            ->assertSessionHasErrors(['login' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $this->post('/acceso-psicologa', $this->credentials(['email' => 'otra@example.com']))
            ->assertSessionHasErrors(['login' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_all_three_fields_are_required(): void
    {
        $this->post('/acceso-psicologa', ['email' => '', 'phone' => '', 'password' => ''])
            ->assertSessionHasErrors(['email', 'phone', 'password']);

        $this->post('/acceso-psicologa', $this->credentials(['phone' => '']))
            ->assertSessionHasErrors(['phone']);

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        for ($i = 0; $i < LoginRequest::MAX_ATTEMPTS; $i++) {
            $this->post('/acceso-psicologa', $this->credentials(['password' => 'incorrecta1']));
        }

        $response = $this->post('/acceso-psicologa', $this->credentials());

        $response->assertSessionHasErrors('login');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('login'));
        $this->assertGuest();
    }

    public function test_remember_me_creates_recaller_cookie(): void
    {
        $response = $this->post('/acceso-psicologa', $this->credentials(['remember' => '1']));

        $response->assertCookie(Auth::guard()->getRecallerName());
        $this->assertNotNull($this->user->fresh()->remember_token);
    }

    public function test_login_without_remember_does_not_create_recaller_cookie(): void
    {
        $this->post('/acceso-psicologa', $this->credentials())
            ->assertCookieMissing(Auth::guard()->getRecallerName());
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs($this->user)
            ->post('/panel-psicologa/cerrar-sesion')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_requires_post(): void
    {
        $this->actingAs($this->user)
            ->get('/panel-psicologa/cerrar-sesion')
            ->assertMethodNotAllowed();
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $this->actingAs($this->user)
            ->get('/acceso-psicologa')
            ->assertRedirect(route('panel.home'));
    }

    public function test_guest_is_redirected_to_login_from_panel(): void
    {
        $this->get('/panel-psicologa')->assertRedirect(route('login'));
    }
}
