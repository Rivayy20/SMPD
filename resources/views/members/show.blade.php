@extends('layouts.app')

@section('title', 'Profil & ID Card - ' . $member->name)

@section('content')
    <x-page-header
        :title="$member->name"
        description="Profil anggota perpustakaan dan pratinjau ID Card digital."
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'Anggota & Kartu' => route('members.index'),
            $member->name => null,
        ]"
    >
        <x-slot:actions>
            <a
                href="{{ route('members.print-card', $member) }}"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800"
            >
                <x-icon name="printer" class="h-4 w-4" />
                <span>Cetak ID Card</span>
            </a>
            <a
                href="{{ route('members.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                <x-icon name="arrow-left" class="h-4 w-4" />
                <span>Kembali</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Preview ID Card Digital -->
        <div class="lg:col-span-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">ID Card Perpustakaan</h3>
                    <a
                        href="{{ route('members.print-card', $member) }}"
                        target="_blank"
                        class="text-xs font-semibold text-blue-900 hover:underline"
                    >
                        Buka Mode Cetak &rarr;
                    </a>
                </div>

                <!-- Desain Kartu Anggota Digital -->
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 via-blue-900 to-indigo-950 p-6 text-white shadow-lg ring-1 ring-white/10">
                    <!-- Logo & Nama Perpustakaan -->
                    <div class="flex items-center justify-between border-b border-white/15 pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/20 text-sky-300 ring-1 ring-sky-400/30">
                                <x-application-logo class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-white">{{ config('app.name', 'SMPD') }}</p>
                                <p class="text-[10px] text-blue-200/80">Perpustakaan Digital</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-white/15 px-2.5 py-0.5 text-[10px] font-semibold tracking-wide text-sky-200">
                            {{ $member->membershipType?->name ?? 'Reguler' }}
                        </span>
                    </div>

                    <!-- Foto & Nama Anggota -->
                    <div class="mt-5 flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-white/15 text-lg font-bold text-white shadow-inner ring-2 ring-white/20">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-base font-bold text-white">{{ $member->name }}</p>
                            <p class="font-mono text-xs text-sky-300">No. {{ $member->membership_number }}</p>
                            <p class="mt-1 text-[11px] text-blue-200/70">
                                Bergabung: {{ $member->join_date ? \Carbon\Carbon::parse($member->join_date)->translatedFormat('d M Y') : '—' }}
                            </p>
                        </div>
                    </div>

                    <!-- Nomor Kartu & Barcode -->
                    <div class="mt-6 rounded-xl bg-white/10 p-3 backdrop-blur-xs">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-blue-200/70">Nomor Kartu</p>
                                <p class="font-mono text-sm font-bold tracking-wider text-white">
                                    {{ $member->card_number ?? $member->membership_number }}
                                </p>
                            </div>
                            <span @class([
                                'rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                'bg-emerald-500/20 text-emerald-300' => $member->status === 'active',
                                'bg-rose-500/20 text-rose-300' => $member->status === 'inactive',
                            ])>
                                {{ $member->status === 'active' ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>

                        <!-- Barcode -->
                        <div class="mt-2.5 flex h-6 w-full items-center justify-center space-x-1 rounded bg-white px-2 py-1">
                            <div class="h-full w-1 bg-black"></div>
                            <div class="h-full w-0.5 bg-black"></div>
                            <div class="h-full w-1.5 bg-black"></div>
                            <div class="h-full w-0.5 bg-black"></div>
                            <div class="h-full w-2 bg-black"></div>
                            <div class="h-full w-0.5 bg-black"></div>
                            <div class="h-full w-1 bg-black"></div>
                            <div class="h-full w-1.5 bg-black"></div>
                            <div class="h-full w-0.5 bg-black"></div>
                            <div class="h-full w-1 bg-black"></div>
                            <div class="h-full w-2 bg-black"></div>
                            <div class="h-full w-0.5 bg-black"></div>
                            <div class="h-full w-1 bg-black"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a
                        href="{{ route('members.print-card', $member) }}"
                        target="_blank"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-900 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800"
                    >
                        <x-icon name="printer" class="h-4 w-4" />
                        <span>Cetak ID Card Anggota</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-7">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Profil Anggota Perpustakaan</h3>

                <dl class="mt-4 divide-y divide-slate-100 text-sm">
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Nama Lengkap</dt>
                        <dd class="col-span-2 font-semibold text-slate-900">{{ $member->name }}</dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Nomor Anggota</dt>
                        <dd class="col-span-2 font-mono font-medium text-blue-900">{{ $member->membership_number }}</dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Nomor Kartu</dt>
                        <dd class="col-span-2 font-mono font-semibold text-slate-800">{{ $member->card_number ?? $member->membership_number }}</dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Nomor Identitas</dt>
                        <dd class="col-span-2 font-mono text-slate-700">{{ $member->identity_number ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Tipe Keanggotaan</dt>
                        <dd class="col-span-2">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-900">
                                {{ $member->membershipType?->name ?? 'Reguler' }}
                            </span>
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="col-span-2">
                            @if ($member->status === 'active')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Tanggal Bergabung</dt>
                        <dd class="col-span-2 text-slate-700">
                            {{ $member->join_date ? \Carbon\Carbon::parse($member->join_date)->translatedFormat('d F Y') : '—' }}
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Email</dt>
                        <dd class="col-span-2 text-slate-700">{{ $member->email ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Telepon / WhatsApp</dt>
                        <dd class="col-span-2 text-slate-700">{{ $member->phone ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 py-3">
                        <dt class="text-slate-500">Alamat</dt>
                        <dd class="col-span-2 text-slate-700">{{ $member->address ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Ketentuan Tipe Keanggotaan</h3>
                <p class="mt-0.5 text-xs text-slate-500">Hak dan kuota peminjaman sesuai tipe {{ $member->membershipType?->name ?? 'Reguler' }}.</p>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-blue-50/70 p-4">
                        <p class="text-xs font-medium text-blue-800">Batas Maksimal Peminjaman</p>
                        <p class="mt-1 text-2xl font-bold text-blue-950">{{ $member->membershipType?->max_borrowed_books ?? 3 }} Buku</p>
                        <p class="mt-0.5 text-xs text-blue-700">Koleksi dapat dipinjam bersamaan</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-medium text-slate-500">Masa Berlaku Kartu</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $member->membershipType?->duration_months ?? 12 }} Bulan</p>
                        <p class="mt-0.5 text-xs text-slate-500">Masa aktif sejak terdaftar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection