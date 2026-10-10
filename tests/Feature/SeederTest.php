<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_admin_and_petugas_accounts(): void
    {
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@smpd.test')->firstOrFail();
        $petugas = User::where('email', 'petugas@smpd.test')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertSame('Administrator', $admin->name);
        $this->assertTrue(Hash::check('password', $admin->password));

        $this->assertSame('petugas', $petugas->role);
        $this->assertSame('Petugas Perpustakaan', $petugas->name);
        $this->assertTrue(Hash::check('password', $petugas->password));
    }

    public function test_database_seeder_delegates_to_user_seeder(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['email' => 'admin@smpd.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'petugas@smpd.test', 'role' => 'petugas']);
    }

    public function test_running_seeders_repeatedly_does_not_duplicate_accounts(): void
    {
        $this->seed(UserSeeder::class);
        $idsBefore = User::orderBy('id')->pluck('id', 'email')->all();

        $this->seed(UserSeeder::class);
        $this->seed();

        $this->assertDatabaseCount('users', 2);
        $this->assertSame($idsBefore, User::orderBy('id')->pluck('id', 'email')->all());
    }

    public function test_reseeding_restores_roles_and_keeps_other_users_untouched(): void
    {
        $this->seed(UserSeeder::class);
        $other = User::factory()->petugas()->create(['email' => 'lain@smpd.test']);

        User::where('email', 'admin@smpd.test')->update(['role' => 'petugas']);

        $this->seed(UserSeeder::class);

        $this->assertSame('admin', User::where('email', 'admin@smpd.test')->value('role'));
        $this->assertSame('petugas', User::where('email', 'petugas@smpd.test')->value('role'));
        $this->assertSame('petugas', $other->fresh()->role);
        $this->assertDatabaseCount('users', 3);
    }
}
