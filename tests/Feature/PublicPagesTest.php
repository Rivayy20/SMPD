<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_introduces_smpd_to_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertSee('<html lang="id"', false);
        $response->assertSee('Kelola Perpustakaan dengan Lebih Mudah');
        $response->assertSee('Masuk ke Sistem');
        $response->assertSee('href="'.route('login').'"', false);
        $response->assertSee('id="fitur"', false);
        $response->assertSee('id="tentang"', false);

        foreach (['Katalog & Buku', 'Anggota & Kartu', 'Penerbit & Supplier', 'Fasilitas & Area'] as $module) {
            $response->assertSee($module);
        }

        $response->assertSee('&copy; '.date('Y'), false);
    }

    public function test_welcome_page_does_not_offer_registration_or_unavailable_module_links(): void
    {
        $response = $this->get('/');

        $response->assertDontSee('Daftar');
        $response->assertDontSee('/register', false);
        $response->assertDontSee('href="/books"', false);
        $response->assertDontSee(route('dashboard'));
    }

    public function test_welcome_page_links_authenticated_users_to_dashboard(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/');

        $response->assertOk();
        $response->assertSee('Buka Dashboard');
        $response->assertSee('href="'.route('dashboard').'"', false);
        $response->assertDontSee('Masuk ke Sistem');
    }

    public function test_login_page_is_in_indonesian_and_keeps_breeze_form_contract(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('<title>Masuk · SMPD</title>', false);
        $response->assertSee('Masuk ke SMPD');
        $response->assertSee('method="POST" action="'.route('login').'"', false);
        $response->assertSee('name="_token"', false);
        $response->assertSee('for="email"', false);
        $response->assertSee('for="password"', false);
        $response->assertSee('autocomplete="current-password"', false);
        $response->assertSee('name="remember"', false);
        $response->assertSee('Kata Sandi');
        $response->assertSee('Ingat saya');
        $response->assertSee('href="'.route('password.request').'"', false);
        $response->assertSee('Kembali ke beranda');
        $response->assertDontSee('Daftar');
        $response->assertDontSee('/register', false);
    }

    public function test_invalid_credentials_show_indonesian_error(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'salah-sandi',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Email atau kata sandi yang Anda masukkan salah.']);

        $this->followingRedirects()
            ->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'salah-sandi'])
            ->assertOk()
            ->assertSee('Email atau kata sandi yang Anda masukkan salah.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('value="'.$user->email.'"', false);
    }

    public function test_empty_login_shows_indonesian_validation_messages(): void
    {
        $response = $this->from('/login')->post('/login', ['email' => '', 'password' => '']);

        $response->assertSessionHasErrors([
            'email' => 'Email wajib diisi.',
            'password' => 'Kata sandi wajib diisi.',
        ]);
    }

    public function test_admin_and_petugas_can_log_in_and_are_redirected_to_dashboard(): void
    {
        foreach (['admin', 'petugas'] as $role) {
            $user = User::factory()->{$role}()->create();

            $this->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect(route('dashboard', absolute: false));

            $this->assertAuthenticatedAs($user);
            $this->post('/logout')->assertRedirect('/');
            $this->assertGuest();
        }
    }
}
