<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_member_pages(): void
    {
        $member = Member::factory()->create();

        $this->get(route('members.index'))->assertRedirect(route('login'));
        $this->get(route('members.create'))->assertRedirect(route('login'));
        $this->post(route('members.store'), [])->assertRedirect(route('login'));
        $this->get(route('members.show', $member))->assertRedirect(route('login'));
        $this->get(route('members.print-card', $member))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_members_index_and_see_data(): void
    {
        $user = User::factory()->create();
        $type = MembershipType::create([
            'name' => 'Pelajar',
            'duration_months' => 12,
            'max_borrowed_books' => 3,
        ]);

        $member = Member::create([
            'membership_number' => 'AGT-2026-0001',
            'card_number' => 'KARTU-2026-0001',
            'name' => 'Ahmad Fauzi',
            'membership_type_id' => $type->id,
            'join_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('members.index'));

        $response->assertOk();
        $response->assertViewIs('members.index');
        $response->assertSee('Anggota & Kartu');
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('AGT-2026-0001');
        $response->assertSee('KARTU-2026-0001');
        $response->assertSee('Pelajar');
        $response->assertSee(route('members.create'));
        $response->assertSee(route('members.print-card', $member));
    }

    public function test_members_index_filters_by_search_query(): void
    {
        $user = User::factory()->create();
        $type = MembershipType::create([
            'name' => 'Reguler',
            'duration_months' => 12,
            'max_borrowed_books' => 3,
        ]);

        Member::create([
            'membership_number' => 'AGT-2026-0010',
            'card_number' => 'KARTU-0010',
            'name' => 'Budi Santoso',
            'membership_type_id' => $type->id,
            'join_date' => '2026-02-01',
            'status' => 'active',
        ]);

        Member::create([
            'membership_number' => 'AGT-2026-0020',
            'card_number' => 'KARTU-0020',
            'name' => 'Dewi Lestari',
            'membership_type_id' => $type->id,
            'join_date' => '2026-02-02',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('members.index', ['search' => 'Budi']));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Dewi Lestari');
    }

    public function test_authenticated_user_can_view_create_member_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('members.create'));

        $response->assertOk();
        $response->assertViewIs('members.create');
        $response->assertSee('Pendaftaran Anggota Baru');
        $response->assertSee('Nomor Kartu Perpustakaan');
        $response->assertSee('Tipe Keanggotaan');
    }

    public function test_user_can_create_new_member_and_redirect_to_show_card(): void
    {
        $user = User::factory()->create();
        $type = MembershipType::create([
            'name' => 'Mahasiswa',
            'duration_months' => 12,
            'max_borrowed_books' => 5,
        ]);

        $postData = [
            'name' => 'Citra Kirana',
            'membership_number' => 'AGT-2026-0077',
            'card_number' => 'KARTU-2026-0077',
            'identity_number' => '3271012345670001',
            'membership_type_id' => $type->id,
            'email' => 'citra@example.com',
            'phone' => '081299887766',
            'address' => 'Jl. Merdeka No. 10 Bandung',
            'join_date' => '2026-03-01',
            'status' => 'active',
        ];

        $response = $this->actingAs($user)->post(route('members.store'), $postData);

        $member = Member::where('membership_number', 'AGT-2026-0077')->firstOrFail();

        $response->assertRedirect(route('members.show', $member));
        $this->assertDatabaseHas('members', [
            'name' => 'Citra Kirana',
            'membership_number' => 'AGT-2026-0077',
            'card_number' => 'KARTU-2026-0077',
            'identity_number' => '3271012345670001',
            'email' => 'citra@example.com',
            'status' => 'active',
        ]);
    }

    public function test_member_creation_requires_mandatory_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('members.store'), []);

        $response->assertSessionHasErrors(['name', 'membership_number', 'membership_type_id', 'join_date', 'status']);
    }

    public function test_user_can_view_member_profile_and_id_card_preview(): void
    {
        $user = User::factory()->create();
        $type = MembershipType::create([
            'name' => 'Guru / Dosen',
            'duration_months' => 24,
            'max_borrowed_books' => 10,
        ]);

        $member = Member::create([
            'membership_number' => 'AGT-2026-0099',
            'card_number' => 'KARTU-0099',
            'name' => 'Dr. Hendra Wijaya',
            'identity_number' => '198001012005011002',
            'email' => 'hendra@univ.ac.id',
            'phone' => '081122334455',
            'address' => 'Komplek Dosen No. 5',
            'membership_type_id' => $type->id,
            'join_date' => '2026-01-10',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('members.show', $member));

        $response->assertOk();
        $response->assertViewIs('members.show');
        $response->assertSee('Dr. Hendra Wijaya');
        $response->assertSee('AGT-2026-0099');
        $response->assertSee('KARTU-0099');
        $response->assertSee('Guru / Dosen');
        $response->assertSee('10 Buku');
        $response->assertSee('24 Bulan');
        $response->assertSee(route('members.print-card', $member));
    }

    public function test_user_can_view_printable_id_card_page(): void
    {
        $user = User::factory()->create();
        $type = MembershipType::create([
            'name' => 'Reguler',
            'duration_months' => 12,
            'max_borrowed_books' => 3,
        ]);

        $member = Member::create([
            'membership_number' => 'AGT-2026-0100',
            'card_number' => 'KARTU-0100',
            'name' => 'Siti Aminah',
            'membership_type_id' => $type->id,
            'join_date' => '2026-01-20',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('members.print-card', $member));

        $response->assertOk();
        $response->assertViewIs('members.print-card');
        $response->assertSee('Siti Aminah');
        $response->assertSee('KARTU-0100');
        $response->assertSee('Cetak Sekarang');
        $response->assertSee('KARTU ANGGOTA PERPUSTAKAAN');
        $response->assertSee('Ketentuan Penggunaan Kartu');
    }

    public function test_sidebar_and_dashboard_render_member_module_as_unlocked_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('href="'.route('members.index').'"', false);
        $response->assertSee('Anggota & Kartu');
    }
}
