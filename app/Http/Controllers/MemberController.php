<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Tampilkan daftar anggota perpustakaan (Read).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $typeId = $request->query('membership_type_id');
        $status = $request->query('status');

        $query = Member::query()->with('membershipType');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%")
                    ->orWhere('card_number', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($typeId) {
            $query->where('membership_type_id', $typeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $members = $query->latest('id')->paginate(10)->withQueryString();

        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::where('status', 'active')->count(),
            'inactive_members' => Member::where('status', 'inactive')->count(),
            'total_types' => MembershipType::count(),
        ];

        $membershipTypes = MembershipType::orderBy('name')->get();

        return view('members.index', [
            'members' => $members,
            'stats' => $stats,
            'membershipTypes' => $membershipTypes,
            'filters' => [
                'search' => $search,
                'membership_type_id' => $typeId,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Form pendaftaran anggota perpustakaan baru (Create).
     */
    public function create(): View
    {
        $this->ensureDefaultMembershipTypesExist();

        $membershipTypes = MembershipType::orderBy('name')->get();

        // Rekomendasi Nomor Anggota & Nomor Kartu otomatis
        $year = date('Y');
        $nextNumber = Member::max('id') + 1;
        $suggestedNumber = sprintf('AGT-%s-%04d', $year, $nextNumber);
        $suggestedCardNumber = sprintf('KARTU-%s-%04d', $year, $nextNumber);

        return view('members.create', [
            'membershipTypes' => $membershipTypes,
            'suggestedNumber' => $suggestedNumber,
            'suggestedCardNumber' => $suggestedCardNumber,
        ]);
    }

    /**
     * Simpan data anggota perpustakaan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'membership_number' => ['required', 'string', 'max:50', 'unique:members,membership_number'],
            'card_number' => ['nullable', 'string', 'max:50', 'unique:members,card_number'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'membership_type_id' => ['required', 'exists:membership_types,id'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'join_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Jika nomor kartu tidak diisi, gunakan nomor anggota sebagai nomor kartu default
        if (blank($validated['card_number'] ?? null)) {
            $validated['card_number'] = $validated['membership_number'];
        }

        $member = Member::create($validated);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Anggota "'.$member->name.'" berhasil didaftarkan. ID Card siap dicetak.');
    }

    /**
     * Tampilkan profil anggota perpustakaan & preview ID Card (Read).
     */
    public function show(Member $member): View
    {
        $member->load('membershipType');

        return view('members.show', [
            'member' => $member,
        ]);
    }

    /**
     * Halaman cetak ID Card anggota perpustakaan (Print View).
     */
    public function printCard(Member $member): View
    {
        $member->load('membershipType');

        return view('members.print-card', [
            'member' => $member,
        ]);
    }

    /**
     * Pastikan tipe keanggotaan default tersedia untuk memudahkan pengisian.
     */
    protected function ensureDefaultMembershipTypesExist(): void
    {
        if (MembershipType::count() === 0) {
            $defaults = [
                [
                    'name' => 'Reguler',
                    'description' => 'Keanggotaan umum standar perpustakaan',
                    'duration_months' => 12,
                    'max_borrowed_books' => 3,
                ],
                [
                    'name' => 'Pelajar / Mahasiswa',
                    'description' => 'Pelajar dan mahasiswa dengan kartu pelajar/KTM',
                    'duration_months' => 12,
                    'max_borrowed_books' => 5,
                ],
                [
                    'name' => 'Guru / Dosen',
                    'description' => 'Tenaga pengajar dan civitas akademika',
                    'duration_months' => 24,
                    'max_borrowed_books' => 10,
                ],
            ];

            foreach ($defaults as $type) {
                MembershipType::firstOrCreate(['name' => $type['name']], $type);
            }
        }
    }
}
