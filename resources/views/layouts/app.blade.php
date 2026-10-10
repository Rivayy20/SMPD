{{--
    Layout utama SMPD untuk semua halaman yang memerlukan login.

    Cara pakai di halaman:
        @extends('layouts.app')
        @section('title', 'Judul Halaman')
        @section('content') ... @endsection

    Opsional: @push('styles') / @push('scripts') untuk CSS/JS khusus halaman.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'SMPD') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')
    </head>
    <body
        class="h-full font-sans antialiased text-slate-800"
        x-data="appShell"
        :class="{ 'overflow-hidden lg:overflow-auto': sidebarOpen }"
        @keydown.escape.window="closeSidebar()"
    >
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-blue-900 focus:shadow"
        >
            Lewati ke konten utama
        </a>

        <x-sidebar />

        <div class="flex min-h-full flex-col lg:pl-72">
            <x-navbar />

            <main id="main-content" class="flex-1" tabindex="-1">
                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    <x-flash-messages />

                    @yield('content')
                </div>
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
