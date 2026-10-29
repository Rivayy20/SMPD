@extends('layouts.app')

@section('title', 'Anggota & Kartu')

@section('content')
    <x-page-header
        title="Anggota & Kartu"
        description="Pengelolaan profil anggota perpustakaan, nomor kartu, tipe keanggotaan, dan cetak ID Card."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Anggota & Kartu' => null]"
    >
        <x-slot:actions>
            <a
                href="{{ route('members.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
            >
                <x-icon name="plus" class="h-4 w-4" />
                <span>Tambah Anggota Baru</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Ringkasan Statistik -->
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Statistik Anggota">
        <x-stat-card
            label="Total Anggota"
            :value="$stats['total_members']"
            icon="id-card"
            hint="Anggota terdaftar"
        />
        <x-stat-card
            label="Anggota Aktif"
            :value="$stats['active_members']"
            icon="check-circle"
            hint="Dapat meminjam buku"
        />
        <x-stat-card
            label="Anggota Nonaktif"
            :value="$stats['inactive_members']"
            icon="exclamation-circle"
            hint="Masa aktif habis / ditangguhkan"
        />
        <x-stat-card
            label="Tipe Keanggotaan"
            :value="$stats['total_types']"
            icon="users"
            hint="Klasifikasi keanggotaan"
        />
    </section>

    <!-- Filter & Pencarian Sederhana -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('members.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <label for="search" class="sr-only">Cari anggota</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama, no. anggota, no. kartu..."
                        class="w-full rounded-lg border-slate-300 pl-9 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-blue-600"
                    />
                </div>
            </div>

            <div class="lg:col-span-3">
                <label for="membership_type_id" class="sr-only">Tipe Keanggotaan</label>
                <select
                    name="membership_type_id"
                    id="membership_type_id"
                    class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-600 focus:ring-blue-600"
                >
                    <option value="">Semua Tipe Keanggotaan</option>
                    @foreach ($membershipTypes as $type)
                        <option value="{{ $type->id }}" @selected(($filters['membership_type_id'] ?? '') == $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="status" class="sr-only">Status</label>
                <select
                    name="status"
                    id="status"
                    class="w-full rounded-lg border-slate-300 text-sm text-slate-800 focus:border-blue-600 focus:ring-blue-600"
                >
                    <option value="">Semua Status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-center gap-2 lg:col-span-2">
                <button
                    type="submit"
                    class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-blue-900 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800"
                >
                    <x-icon name="funnel" class="h-4 w-4" />
                    <span>Cari</span>
                </button>
                @if (! empty($filters['search']) || ! empty($filters['membership_type_id']) || ! empty($filters['status']))
                    <a
                        href="{{ route('members.index') }}"
                        class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                        title="Reset Pencarian"
                    >
                        <x-icon name="arrow-path" class="h-4 w-4" />
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Profil Anggota & Kartu (Read) -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:flex sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Daftar Anggota Perpustakaan</h2>
                <p class="mt-0.5 text-xs text-slate-500">Total {{ $members->total() }} anggota terdaftar dalam sistem</p>
            </div>
        </div>

        @if ($members->isEmpty())
            <div class="flex flex-col items-center justify-center px-4 py-14 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-900">
                    <x-icon name="id-card" class="h-6 w-6" />
                </div>
                <h3 class="mt-3 text-base font-semibold text-slate-900">Belum ada data anggota</h3>
                <p class="mt-1 max-w-sm text-sm text-slate-500">
                    @if (! empty($filters['search']) || ! empty($filters['membership_type_id']) || ! empty($filters['status']))
                        Tidak ada data yang cocok dengan kriteria pencarian.
                    @else
                        Mulai dengan mendaftarkan anggota baru untuk menerbitkan nomor kartu perpustakaan.
                    @endif
                </p>
                <div class="mt-5">
                    @if (! empty($filters['search']) || ! empty($filters['membership_type_id']) || ! empty($filters['status']))
                        <a href="{{ route('members.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <x-icon name="arrow-path" class="h-4 w-4" />
                            <span>Reset Pencarian</span>
                        </a>
                    @else
                        <a href="{{ route('members.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                            <x-icon name="plus" class="h-4 w-4" />
                            <span>Tambah Anggota Pertama</span>
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Profil Anggota</th>
                            <th scope="col" class="px-4 py-3.5">Nomor Kartu</th>
                            <th scope="col" class="px-4 py-3.5">Tipe Keanggotaan</th>
                            <th scope="col" class="px-4 py-3.5">Kontak</th>
                            <th scope="col" class="px-4 py-3.5">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Aksi & ID Card</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($members as $member)
                            <tr class="transition hover:bg-slate-50/75">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-900">
                                            {{ strtoupper(substr($member->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('members.show', $member) }}" class="font-semibold text-slate-900 hover:text-blue-900">
                                                {{ $member->name }}
                                            </a>
                                            <p class="font-mono text-xs text-slate-500">
                                                No. {{ $member->membership_number }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold text-slate-800">
                                        <x-icon name="id-card" class="h-3.5 w-3.5 text-slate-500" />
                                        {{ $member->card_number ?? $member->membership_number }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-800">
                                        {{ $member->membershipType?->name ?? 'Reguler' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-600">
                                    <p>{{ $member->phone ?? '—' }}</p>
                                    @if ($member->email)
                                        <p class="text-slate-400">{{ $member->email }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    @if ($member->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                    <div class="inline-flex items-center gap-2">
                                        <a
                                            href="{{ route('members.show', $member) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            title="Lihat Profil Anggota"
                                        >
                                            <x-icon name="eye" class="h-3.5 w-3.5 text-slate-500" />
                                            <span>Profil</span>
                                        </a>

                                        <a
                                            href="{{ route('members.print-card', $member) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg bg-blue-900 px-2.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-blue-800"
                                            title="Cetak ID Card Anggota"
                                        >
                                            <x-icon name="printer" class="h-3.5 w-3.5" />
                                            <span>Cetak ID Card</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($members->hasPages())
                <div class="border-t border-slate-200 px-5 py-3">
                    {{ $members->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
