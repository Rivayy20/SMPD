<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\MembershipType;
use App\Models\Publisher;
use App\Models\ReadingArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_shared_layout_and_shows_current_user(): void
    {
        $petugas = User::factory()->petugas()->create(['name' => 'Siti Petugas']);

        $response = $this->actingAs($petugas)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard.index');
        $response->assertSee('Selamat datang, Siti Petugas');
        $response->assertSee('Petugas');
        $response->assertSee('id="app-sidebar"', false);
        $response->assertSee('Perpustakaan Digital');
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertSee(route('profile.edit'));
    }

    public function test_user_management_menu_is_only_visible_to_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee('Administrasi');

        $this->actingAs(User::factory()->petugas()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('User Management')
            ->assertViewHas('usersByRole', null);
    }

    public function test_statistics_are_zero_when_database_is_empty(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('stats', fn (array $stats) => $stats['books'] === 0
            && $stats['members'] === 0
            && $stats['publishers'] === 0
            && $stats['reading_areas'] === 0);
        $response->assertSee('Belum ada data buku');
    }

    public function test_statistics_come_from_database(): void
    {
        $category = Category::create(['name' => 'Fiksi']);
        $publisher = Publisher::create(['name' => 'Gramedia', 'type' => 'publisher']);
        Publisher::create(['name' => 'Distributor Jaya', 'type' => 'distributor']);

        Book::create([
            'title' => 'Laskar Pelangi',
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2005,
            'stock' => 7,
        ]);

        $type = MembershipType::create([
            'name' => 'Reguler',
            'duration_months' => 12,
            'max_borrowed_books' => 3,
        ]);

        Member::create([
            'membership_number' => 'M-001',
            'name' => 'Budi',
            'join_date' => now()->toDateString(),
            'membership_type_id' => $type->id,
        ]);

        ReadingArea::create(['name' => 'Ruang Baca 1', 'floor_number' => 1, 'capacity' => 20]);

        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('stats', fn (array $stats) => $stats['books'] === 1
            && $stats['book_copies'] === 7
            && $stats['members'] === 1
            && $stats['publishers'] === 2
            && $stats['publisher_count'] === 1
            && $stats['distributor_count'] === 1
            && $stats['reading_areas'] === 1);
        $response->assertSee('Laskar Pelangi');
        $response->assertSee('Fiksi');
    }

    public function test_modules_without_routes_are_rendered_as_placeholders(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Katalog & Buku');
        $response->assertSee('Segera hadir');
        $response->assertDontSee('href="/books"', false);
    }

    public function test_modules_become_links_once_their_route_exists(): void
    {
        Route::middleware(['web', 'auth'])->get('/books', fn () => 'Books')->name('books.index');
        Route::getRoutes()->refreshNameLookups();

        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk();
        $response->assertSee('href="'.route('books.index').'"', false);
    }

    public function test_profile_page_uses_shared_layout(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/profile');

        $response->assertOk();
        $response->assertSee('id="app-sidebar"', false);
        $response->assertSee('Profil Saya');
    }
}
