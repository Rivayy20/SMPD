<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Admin-only route registered for this test class only, so no
     * testing endpoint is left in routes/web.php.
     */
    private const ADMIN_URI = '/_test/admin-only';

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin'])
            ->get(self::ADMIN_URI, fn () => 'Admin Area');
    }

    public function test_guests_are_redirected_to_login_from_admin_routes(): void
    {
        $response = $this->get(self::ADMIN_URI);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(self::ADMIN_URI);

        $response->assertOk();
        $response->assertSee('Admin Area');
    }

    public function test_petugas_is_forbidden_from_admin_routes(): void
    {
        $petugas = User::factory()->petugas()->create();

        $response = $this->actingAs($petugas)->get(self::ADMIN_URI);

        $response->assertForbidden();
    }

    public function test_petugas_cannot_gain_admin_access_through_request_input(): void
    {
        $petugas = User::factory()->petugas()->create();

        $this->actingAs($petugas)
            ->get(self::ADMIN_URI.'?role=admin')
            ->assertForbidden();

        $this->actingAs($petugas)
            ->withHeader('X-Role', 'admin')
            ->get(self::ADMIN_URI)
            ->assertForbidden();

        $this->assertSame('petugas', $petugas->fresh()->role);
    }

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_admin_and_petugas_can_access_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs(User::factory()->petugas()->create())
            ->get('/dashboard')
            ->assertOk();
    }
}
