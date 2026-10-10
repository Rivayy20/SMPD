@extends('layouts.public')

@section('title', 'Sistem Manajemen Perpustakaan Digital')

@php
    // Nama, ikon, dan deskripsi modul diambil dari sumber yang sama dengan sidebar dashboard.
    $modules = collect(config('navigation.sidebar', []))
        ->flatMap(fn (array $group) => $group['items'] ?? [])
        ->where('module', true)
        ->values();

    $spines = [
        ['h-24', 'w-6', 'bg-blue-900'],
        ['h-28', 'w-8', 'bg-blue-700'],
        ['h-20', 'w-5', 'bg-sky-300'],
        ['h-28', 'w-7', 'bg-blue-950'],
        ['h-24', 'w-6', 'bg-slate-300'],
        ['h-16', 'w-9', 'bg-blue-800'],
        ['h-24', 'w-5', 'bg-sky-500'],
        ['h-28', 'w-7', 'bg-blue-900'],
        ['h-20', 'w-6', 'bg-blue-200'],
        ['h-24', 'w-8', 'bg-blue-700'],
    ];
@endphp

@section('content')
    <!-- Hero -->
    <section id="beranda" class="scroll-mt-16 bg-slate-50">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-1 text-xs font-semibold text-blue-800">
                    <x-icon name="book" class="h-4 w-4" />
                    Sistem Informasi Perpustakaan Digital
                </span>

                <h1 class="mt-5 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                    Kelola Perpustakaan dengan Lebih Mudah
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
                    SMPD dirancang untuk membantu petugas mengelola katalog buku, data anggota,
                    penerbit, serta fasilitas perpustakaan melalui satu sistem yang terintegrasi.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                            Buka Dashboard
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                            Masuk ke Sistem
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @endauth
                    <a href="#fitur" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                        Lihat Fitur
                    </a>
                </div>
            </div>

            <!-- Visual pendukung (dekoratif) -->
            <div class="mx-auto w-full max-w-lg lg:max-w-none" aria-hidden="true">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between">
                        <x-brand subtitle="Katalog Perpustakaan" />
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-800">Rak Koleksi</span>
                    </div>

                    <div class="mt-8 flex h-32 items-end justify-center gap-1.5 border-b-4 border-blue-950 px-2 sm:gap-2">
                        @foreach ($spines as [$height, $width, $color])
                            {{-- Layar kecil hanya menampilkan 7 buku agar rak tidak melebihi lebar layar. --}}
                            <span class="{{ $height }} {{ $width }} {{ $color }} {{ $loop->index >= 7 ? 'hidden sm:flex' : 'flex' }} shrink-0 flex-col items-center gap-1 rounded-t-sm pt-3">
                                <span class="h-0.5 w-3/5 rounded-full bg-white/50"></span>
                                <span class="h-0.5 w-3/5 rounded-full bg-white/50"></span>
                            </span>
                        @endforeach
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        @foreach ($modules as $module)
                            <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
                                    <x-icon :name="$module['icon']" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0 text-xs font-semibold text-slate-700 sm:text-sm">{{ $module['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur / ruang lingkup -->
    <section id="fitur" class="scroll-mt-16 bg-white" aria-labelledby="fitur-heading">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-blue-800">Ruang Lingkup Sistem</p>
                <h2 id="fitur-heading" class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                    Empat modul utama pengelolaan perpustakaan
                </h2>
                <p class="mt-4 text-base text-slate-600">
                    SMPD disusun dalam empat modul yang mencakup kebutuhan administrasi perpustakaan sehari-hari.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($modules as $module)
                    <div class="rounded-xl border border-slate-200 bg-white p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
                            <x-icon :name="$module['icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-5 text-base font-semibold text-slate-900">{{ $module['label'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $module['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Tentang -->
    <section id="tentang" class="scroll-mt-16 border-t border-slate-200 bg-slate-50" aria-labelledby="tentang-heading">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-2 lg:gap-16 lg:px-8">
            <div>
                <p class="text-sm font-semibold text-blue-800">Tentang SMPD</p>
                <h2 id="tentang-heading" class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                    Informasi perpustakaan yang tertata dan mudah diakses
                </h2>
                <p class="mt-4 text-base leading-relaxed text-slate-600">
                    SMPD (Sistem Manajemen Perpustakaan Digital) bertujuan membantu pengelolaan informasi
                    perpustakaan agar lebih terstruktur. Data koleksi, anggota, penerbit, dan fasilitas
                    dikelola di satu tempat sehingga mudah ditemukan dan diperbarui oleh petugas yang berwenang.
                </p>
            </div>

            <ul class="space-y-4">
                @foreach ([
                    ['shield', 'Akses berbasis peran', 'Hak akses dibedakan antara Admin dan Petugas sesuai tanggung jawab masing-masing.'],
                    ['database', 'Data yang saling terhubung', 'Buku, penulis, kategori, penerbit, anggota, dan fasilitas tersimpan dalam satu basis data yang terstruktur.'],
                    ['home', 'Satu pintu pengelolaan', 'Seluruh modul diakses melalui satu dashboard dengan tampilan yang konsisten.'],
                ] as [$icon, $title, $description])
                    <li class="flex gap-4 rounded-xl border border-slate-200 bg-white p-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-900 text-white">
                            <x-icon :name="$icon" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
                            <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $description }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
