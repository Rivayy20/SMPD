{{--
    Navbar atas. Judul diambil dari @section('title') halaman aktif.
--}}
@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">
    <!-- Tombol buka sidebar (mobile/tablet) -->
    <button
        type="button"
        x-ref="sidebarToggle"
        class="-ml-2 rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 lg:hidden"
        aria-controls="app-sidebar"
        :aria-expanded="sidebarOpen.toString()"
        @click="openSidebar()"
    >
        <span class="sr-only">Buka menu</span>
        <x-icon name="bars" class="h-6 w-6" />
    </button>

    <p class="min-w-0 flex-1 truncate text-base font-semibold text-slate-900">
        @yield('title', 'Dashboard')
    </p>

    <!-- Dropdown akun -->
    <div
        class="relative"
        x-data="{ open: false }"
        @click.outside="open = false"
        @focusout="if (! $el.contains($event.relatedTarget)) open = false"
        @keydown.escape.stop="if (open) { open = false; $refs.accountButton.focus() }"
    >
        <button
            type="button"
            x-ref="accountButton"
            class="flex items-center gap-3 rounded-lg p-1.5 text-left hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
            aria-controls="account-menu"
            :aria-expanded="open.toString()"
            @click="open = ! open"
        >
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-sm font-semibold text-white" aria-hidden="true">
                {{ $initials }}
            </span>
            <span class="hidden min-w-0 leading-tight sm:block">
                <span class="block max-w-[12rem] truncate text-sm font-medium text-slate-900">{{ $user->name }}</span>
                <span class="block text-xs text-slate-500">{{ ucfirst($user->role) }}</span>
            </span>
            <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block" />
            <span class="sr-only sm:hidden">Menu akun {{ $user->name }}</span>
        </button>

        <div
            id="account-menu"
            x-cloak
            x-show="open"
            x-transition.origin.top.right
            class="absolute right-0 mt-2 w-64 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
        >
            <div class="border-b border-slate-100 px-4 py-3">
                <p class="truncate text-sm font-medium text-slate-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                <span class="mt-2 inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-800">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <div class="py-1">
                <a
                    href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none"
                >
                    <x-icon name="user-circle" class="h-5 w-5 text-slate-400" />
                    Profil Saya
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 focus:bg-red-50 focus:outline-none"
                    >
                        <x-icon name="logout" class="h-5 w-5" />
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
