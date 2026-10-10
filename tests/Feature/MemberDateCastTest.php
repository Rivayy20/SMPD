<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipType;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberDateCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_join_date_is_read_as_a_date_object(): void
    {
        $member = $this->createMember('2026-10-01')->fresh();

        $this->assertInstanceOf(CarbonInterface::class, $member->join_date);
        $this->assertSame('01/10/2026', $member->join_date->format('d/m/Y'));
        $this->assertSame('2026-10-01', $member->join_date->toDateString());
    }

    public function test_join_date_is_stored_as_a_plain_date_matching_the_migration(): void
    {
        $member = $this->createMember('2026-10-01');

        // Kolom join_date bertipe DATE, jadi tidak boleh tersimpan dengan komponen jam.
        $this->assertSame('2026-10-01', DB::table('members')->where('id', $member->id)->value('join_date'));
        $this->assertSame(1, Member::where('join_date', '2026-10-01')->count());
    }

    public function test_join_date_accepts_form_strings_and_date_objects(): void
    {
        $member = $this->createMember('2026-01-15');

        $member->update(['join_date' => Carbon::create(2026, 2, 20, 14, 30)]);

        $this->assertSame('2026-02-20', DB::table('members')->where('id', $member->id)->value('join_date'));
        $this->assertSame('20/02/2026', $member->fresh()->join_date->format('d/m/Y'));
    }

    private function createMember(string $joinDate): Member
    {
        $type = MembershipType::create([
            'name' => 'Reguler',
            'duration_months' => 12,
            'max_borrowed_books' => 3,
        ]);

        return Member::create([
            'membership_number' => 'M-'.uniqid(),
            'name' => 'Budi Santoso',
            'join_date' => $joinDate,
            'membership_type_id' => $type->id,
        ]);
    }
}
