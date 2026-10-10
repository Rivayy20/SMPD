{{--
    Navbar halaman publik. Hanya menautkan bagian halaman dan route yang tersedia.
--}}
@php
    $links = [
        'Beranda' => url('/#beranda'),
        'Fitur' => url('/#fitur'),
        'Tentang' => url('/#tentang'),
    ];
@endphp

<header
    class="sticky top-0 z-40 border-b border-slate-200 bg-white"
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
        <a href="{{ url('/') }}" class="min-w-0 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
            <x-brand subtitle="Sistem Manajemen Perpustakaan Digital" />
        </a>

        <div class="hidden items-center gap-8 md:flex">
            <ul class="flex items-center gap-1">
                @foreach ($links as $label => $href)
                    <li>
                        <a href="{{ $href }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>

            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    Masuk
                </a>
            @endauth
        </div>

        <!-- Tombol menu mobile -->
        <button
            type="button"
            class="-mr-2 rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 md:hidden"
            aria-controls="public-mobile-menu"
            :aria-expanded="open.toString()"
            @click="open = ! open"
        >
            <span class="sr-only" x-text="open ? 'Tutup menu' : 'Buka menu'">Buka menu</span>
            <x-icon name="bars" class="h-6 w-6" x-show="! open" />
            <x-icon name="x-mark" class="h-6 w-6" x-cloak x-show="open" />
        </button>
    </nav>

    <!-- Menu mobile -->
    <div id="public-mobile-menu" x-cloak x-show="open" x-transition.opacity class="border-t border-slate-200 bg-white md:hidden">
        <ul class="space-y-1 px-4 py-3">
            @foreach ($links as $label => $href)
                <li>
                    <a href="{{ $href }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600" @click="open = false">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="border-t border-slate-200 px-4 py-3">
            @auth
                <a href="{{ route('dashboard') }}" class="block rounded-lg bg-blue-900 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="block rounded-lg bg-blue-900 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                    Masuk
                </a>
            @endauth
        </div>
    </div>
</header>
