{{--
    Layout halaman publik (tanpa login): navbar publik, konten, footer.

        @extends('layouts.public')
        @section('title', 'Judul')
        @section('content') ... @endsection
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="motion-safe:scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="SMPD — Sistem Manajemen Perpustakaan Digital untuk pengelolaan katalog buku, anggota, penerbit, dan fasilitas perpustakaan.">

        <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'SMPD') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')
    </head>
    <body class="min-h-screen bg-white font-sans text-slate-800 antialiased">
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-blue-900 focus:shadow"
        >
            Lewati ke konten utama
        </a>

        <x-public-navbar />

        <main id="main-content" tabindex="-1">
            @yield('content')
        </main>

        <x-public-footer />

        @stack('scripts')
    </body>
</html>
