{{--
    Layout halaman autentikasi (login, lupa kata sandi, dll.).
    Desktop: panel identitas SMPD + formulir. Mobile: satu kolom.

        <x-guest-layout>
            <x-slot:title>Masuk</x-slot:title>
            ...formulir...
        </x-guest-layout>
--}}
@php
    $modules = collect(config('navigation.sidebar', []))
        ->flatMap(fn (array $group) => $group['items'] ?? [])
        ->where('module', true)
        ->values();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@isset($title){{ $title }} · @endisset{{ config('app.name', 'SMPD') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-800 antialiased">
        <div class="flex min-h-full">
            <!-- Panel identitas (desktop) -->
            <aside class="hidden w-[44%] max-w-xl shrink-0 flex-col justify-between bg-blue-950 px-10 py-10 text-blue-100 lg:flex xl:px-14">
                <a href="{{ url('/') }}" class="self-start rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400">
                    <x-brand dark />
                </a>

                <div>
                    <p class="text-3xl font-semibold leading-tight tracking-tight text-white">
                        Sistem Manajemen<br>Perpustakaan Digital
                    </p>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-blue-200/80">
                        Kelola katalog buku, anggota, penerbit, dan fasilitas perpustakaan dalam satu sistem.
                    </p>

                    <ul class="mt-8 space-y-3">
                        @foreach ($modules as $module)
                            <li class="flex items-center gap-3 text-sm text-blue-100/90">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-sky-300">
                                    <x-icon :name="$module['icon']" class="h-4 w-4" />
                                </span>
                                {{ $module['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="text-xs text-blue-300/70">
                    &copy; {{ date('Y') }} {{ config('app.name', 'SMPD') }}. Akses khusus Admin dan Petugas.
                </p>
            </aside>

            <!-- Area formulir -->
            <main class="flex flex-1 flex-col items-center justify-center px-4 py-10 sm:px-6">
                <div class="w-full max-w-md">
                    <a href="{{ url('/') }}" class="mb-8 inline-flex rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 lg:hidden">
                        <x-brand />
                    </a>

                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        {{ $slot }}
                    </div>

                    @isset($footer)
                        <div class="mt-6">
                            {{ $footer }}
                        </div>
                    @endisset
                </div>
            </main>
        </div>
    </body>
</html>
