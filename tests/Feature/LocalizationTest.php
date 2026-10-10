<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_locale_is_indonesian_with_english_fallback(): void
    {
        $this->assertSame('id', app()->getLocale());
        $this->assertSame('en', config('app.fallback_locale'));
    }

    public function test_every_translatable_string_in_views_has_an_indonesian_translation(): void
    {
        $translations = json_decode(File::get(lang_path('id.json')), true, flags: JSON_THROW_ON_ERROR);
        $missing = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            preg_match_all("/__\(\s*'((?:[^'\\\\]|\\\\.)*)'|__\(\s*\"((?:[^\"\\\\]|\\\\.)*)\"/", $file->getContents(), $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $key = isset($match[2]) && $match[2] !== '' ? stripcslashes($match[2]) : str_replace("\\'", "'", $match[1]);

                // Key grup seperti 'pagination.next' diterjemahkan lewat file PHP di lang/id/.
                if (preg_match('/^[a-z_]+\.[a-z_.]+$/', $key)) {
                    continue;
                }

                if (! array_key_exists($key, $translations)) {
                    $missing[] = $file->getRelativePathname().': '.$key;
                }
            }
        }

        $this->assertSame([], $missing);
    }

    public function test_indonesian_validation_file_covers_every_framework_rule(): void
    {
        $english = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
        $indonesian = require lang_path('id/validation.php');

        $missing = array_diff(
            array_keys(Arr::dot(Arr::except($english, ['custom', 'attributes']))),
            array_keys(Arr::dot(Arr::except($indonesian, ['custom', 'attributes']))),
        );

        $this->assertSame([], array_values($missing));
    }

    public function test_forgot_password_page_is_in_indonesian(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('<title>Lupa Kata Sandi · SMPD</title>', false)
            ->assertSee('Masukkan alamat email akun Anda.')
            ->assertSee('Kirim Tautan Atur Ulang Kata Sandi')
            ->assertSee('Kembali ke halaman masuk')
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('Forgot your password?')
            ->assertDontSee('Email Password Reset Link');
    }

    public function test_reset_password_page_is_in_indonesian(): void
    {
        $this->get('/reset-password/token-uji?email=uji@smpd.test')
            ->assertOk()
            ->assertSee('Atur Ulang Kata Sandi')
            ->assertSee('Buat kata sandi baru untuk akun Anda.')
            ->assertSee('Kata Sandi')
            ->assertSee('Konfirmasi Kata Sandi')
            ->assertSee('action="'.route('password.store').'"', false)
            // DontSeeText: komentar HTML seperti <!-- Confirm Password --> bukan teks yang terlihat.
            ->assertDontSeeText('Confirm Password')
            ->assertDontSeeText('Reset Password');
    }

    public function test_confirm_password_page_is_in_indonesian(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/confirm-password')
            ->assertOk()
            ->assertSee('Konfirmasi Kata Sandi')
            ->assertSee('Halaman ini termasuk area aman.')
            ->assertSee('Konfirmasi')
            ->assertDontSee('This is a secure area');
    }

    public function test_verify_email_page_is_in_indonesian(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get('/verify-email')
            ->assertOk()
            ->assertSee('Verifikasi Email')
            ->assertSee('Kirim Ulang Email Verifikasi')
            ->assertSee('Keluar')
            ->assertDontSee('Thanks for signing up')
            ->assertDontSee('Log Out');
    }

    public function test_wrong_password_on_confirm_page_shows_indonesian_error(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/confirm-password', ['password' => 'salah-sandi'])
            ->assertSessionHasErrors(['password' => 'Kata sandi yang Anda masukkan salah.']);
    }

    public function test_profile_page_is_in_indonesian(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/profile');

        $response->assertOk();

        foreach ([
            'Profil Saya', 'Informasi Profil', 'Perbarui informasi profil dan alamat email akun Anda.',
            'Nama', 'Simpan', 'Ubah Kata Sandi', 'Kata Sandi Saat Ini', 'Kata Sandi Baru',
            'Konfirmasi Kata Sandi', 'Hapus Akun', 'Apakah Anda yakin ingin menghapus akun?', 'Batal',
        ] as $text) {
            $response->assertSee($text);
        }

        foreach ([
            'Profile Information', 'Update Password', 'Current Password', 'New Password',
            'Delete Account', 'Are you sure', 'Cancel',
        ] as $english) {
            $response->assertDontSee($english);
        }
    }

    public function test_profile_update_shows_indonesian_success_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->followingRedirects()
            ->patch('/profile', ['name' => 'Nama Baru', 'email' => $user->email])
            ->assertOk()
            ->assertSee('Berhasil disimpan.');

        $this->assertSame('Nama Baru', $user->fresh()->name);
    }

    public function test_profile_validation_messages_are_in_indonesian(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', ['name' => '', 'email' => 'bukan-email'])
            ->assertSessionHasErrors([
                'name' => 'Nama wajib diisi.',
                'email' => 'Email harus berupa alamat email yang valid.',
            ]);
    }

    public function test_password_update_validation_messages_are_in_indonesian(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'salah-sandi',
                'password' => 'sandi-baru-123',
                'password_confirmation' => 'tidak-sama',
            ])
            ->assertSessionHasErrorsIn('updatePassword', [
                'current_password' => 'Kata sandi salah.',
                'password' => 'Konfirmasi kata sandi tidak cocok.',
            ]);
    }

    public function test_forgot_password_statuses_are_in_indonesian(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => 'tidak.ada@smpd.test'])
            ->assertSessionHasErrors(['email' => 'Kami tidak menemukan pengguna dengan alamat email tersebut.']);

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', 'Tautan untuk mengatur ulang kata sandi telah dikirim ke email Anda.');
    }

    public function test_password_reset_email_is_in_indonesian(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $mail = $notification->toMail($user);
            $html = (string) $mail->render();

            return $mail->subject === 'Atur ulang kata sandi Anda'
                && $mail->actionText === 'Atur Ulang Kata Sandi'
                && str_contains($html, 'Halo!')
                && str_contains($html, 'Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi')
                && str_contains($html, 'Salam,')
                && ! str_contains($html, 'You are receiving this email');
        });
    }

    public function test_email_verification_message_is_in_indonesian(): void
    {
        $user = User::factory()->unverified()->create();
        $mail = (new VerifyEmail)->toMail($user);

        $this->assertSame('Verifikasi alamat email Anda', $mail->subject);
        $this->assertSame('Verifikasi Alamat Email', $mail->actionText);
        $this->assertContains('Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.', $mail->introLines);
    }

    public function test_error_pages_are_in_indonesian(): void
    {
        Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only', fn () => 'Admin Area');

        $this->actingAs(User::factory()->petugas()->create())
            ->get('/_test/admin-only')
            ->assertForbidden()
            ->assertSee('Akses Ditolak')
            ->assertDontSee('Forbidden');

        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertDontSee('Not Found');
    }
}
