@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php($user = auth()->user())

    <!-- Welcome section -->
    <section class="rounded-xl bg-blue-900 px-6 py-6 text-white sm:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm text-blue-200">Dashboard</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">Selamat datang, {{ $user->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-blue-100">
                    Pantau ringkasan koleksi buku, anggota, penerbit, dan area baca
                    Perpustakaan Digital dari satu tempat.
                </p>
            </div>
            <span class="inline-flex shrink-0 items-center gap-2 self-start rounded-full bg-white/10 px-3 py-1 text-sm font-medium text-white sm:self-center">
                <x-icon :name="$user->role === 'admin' ? 'shield' : 'user-circle'" class="h-4 w-4" />
                {{ ucfirst($user->role) }}
            </span>
        </div>
    </section>

    <!-- Statistik ringkas -->
    <section class="mt-6" aria-labelledby="stats-heading">
        <h2 id="stats-heading" class="sr-only">Statistik ringkas</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card
                label="Total Buku"
                :value="$stats['books']"
                icon="book"
                :hint="number_format($stats['book_copies'], 0, ',', '.') . ' eksemplar tersimpan'"
            />
            <x-stat-card
                label="Total Anggota"
                :value="$stats['members']"
                icon="id-card"
                :hint="number_format($stats['active_members'], 0, ',', '.') . ' anggota aktif'"
            />
            <x-stat-card
                label="Penerbit & Distributor"
                :value="$stats['publishers']"
                icon="truck"
                :hint="$stats['publisher_count'] . ' penerbit · ' . $stats['distributor_count'] . ' distributor'"
            />
            <x-stat-card
                label="Total Area Baca"
                :value="$stats['reading_areas']"
                icon="map"
                :hint="$stats['available_reading_areas'] . ' area tersedia'"
            />
        </div>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Akses cepat -->
        <section class="lg:col-span-2" aria-labelledby="modules-heading">
            <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <h2 id="modules-heading" class="text-base font-semibold text-slate-900">Akses Cepat</h2>
                <p class="mt-1 text-sm text-slate-500">Buka modul pengelolaan perpustakaan.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($modules as $module)
                        @if ($module['url'])
                            <a
                                href="{{ $module['url'] }}"
                                class="group flex items-start gap-4 rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                            >
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
                                    <x-icon :name="$module['icon']" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-slate-900">{{ $module['label'] }}</span>
                                    <span class="mt-1 block text-sm text-slate-500">{{ $module['description'] ?? '' }}</span>
                                </span>
                                <x-icon name="arrow-right" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-blue-800" />
                            </a>
                        @else
                            <div class="flex items-start gap-4 rounded-lg border border-dashed border-slate-200 p-4" aria-disabled="true">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                    <x-icon :name="$module['icon']" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-700">{{ $module['label'] }}</span>
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">Segera hadir</span>
                                    </span>
                                    <span class="mt-1 block text-sm text-slate-500">{{ $module['description'] ?? '' }}</span>
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <div class="space-y-6">
            <!-- Buku terbaru (data aktual) -->
            <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="latest-books-heading">
                <h2 id="latest-books-heading" class="text-base font-semibold text-slate-900">Buku Terbaru</h2>
                <p class="mt-1 text-sm text-slate-500">Lima buku terakhir yang ditambahkan.</p>

                @if ($latestBooks->isEmpty())
                    <div class="mt-5 flex flex-col items-center rounded-lg border border-dashed border-slate-200 px-4 py-8 text-center">
                        <x-icon name="archive" class="h-8 w-8 text-slate-300" />
                        <p class="mt-3 text-sm font-medium text-slate-700">Belum ada data buku</p>
                        <p class="mt-1 text-xs text-slate-500">Buku yang ditambahkan melalui modul Katalog akan tampil di sini.</p>
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-slate-100">
                        @foreach ($latestBooks as $book)
                            <li class="py-3">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $book->title }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-500">
                                    {{ $book->category?->name ?? '-' }} · {{ $book->publisher?->name ?? '-' }} · {{ $book->publication_year }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <!-- Khusus Admin -->
            @if ($usersByRole !== null)
                <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="admin-heading">
                    <div class="flex items-center gap-2">
                        <x-icon name="shield" class="h-5 w-5 text-blue-800" />
                        <h2 id="admin-heading" class="text-base font-semibold text-slate-900">Administrasi</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">Ringkasan akun pengguna sistem.</p>

                    <dl class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <dt class="text-xs text-slate-500">Admin</dt>
                            <dd class="mt-1 text-xl font-semibold text-slate-900">{{ $usersByRole->get('admin', 0) }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <dt class="text-xs text-slate-500">Petugas</dt>
                            <dd class="mt-1 text-xl font-semibold text-slate-900">{{ $usersByRole->get('petugas', 0) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-4 text-xs text-slate-500">
                        Pengelolaan akun tersedia melalui menu User Management setelah modul tersebut selesai dikembangkan.
                    </p>
                </section>
            @endif
        </div>
    </div>
@endsection
