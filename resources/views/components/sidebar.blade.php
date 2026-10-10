{{--
    Sidebar utama. Daftar menu diatur di config/navigation.php,
    jangan menambahkan menu langsung di file ini.
--}}
<div>
    <!-- Backdrop (mobile) -->
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
        @click="closeSidebar()"
        aria-hidden="true"
    ></div>

    <aside
        id="app-sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-blue-950 text-blue-100 transition-transform duration-200 ease-in-out lg:translate-x-0"
        :class="{ '-translate-x-full': ! sidebarOpen }"
        :inert="! sidebarOpen && ! isDesktop"
        aria-label="Navigasi utama"
    >
        <!-- Identitas aplikasi -->
        <div class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-white/10 px-5">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-500/15 text-sky-300">
                    <x-application-logo class="h-5 w-5" />
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block text-base font-bold tracking-wide text-white">{{ config('app.name', 'SMPD') }}</span>
                    <span class="block truncate text-xs text-blue-200/80">Perpustakaan Digital</span>
                </span>
            </a>

            <button
                type="button"
                x-ref="sidebarClose"
                class="rounded-lg p-2 text-blue-200 hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 lg:hidden"
                @click="closeSidebar()"
            >
                <span class="sr-only">Tutup menu</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        <!-- Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-5">
            @foreach ($groups as $group)
                <div @class(['mt-6' => ! $loop->first])>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-blue-300/70">
                        {{ $group['heading'] }}
                    </p>

                    <ul class="mt-2 space-y-1">
                        @foreach ($group['items'] as $item)
                            <li>
                                @if ($item['url'])
                                    <a
                                        href="{{ $item['url'] }}"
                                        @if ($item['active']) aria-current="page" @endif
                                        @class([
                                            'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400',
                                            'bg-white/10 text-white' => $item['active'],
                                            'text-blue-100/90 hover:bg-white/5 hover:text-white' => ! $item['active'],
                                        ])
                                    >
                                        <x-icon :name="$item['icon']" @class([
                                            'h-5 w-5 shrink-0',
                                            'text-sky-300' => $item['active'],
                                            'text-blue-300/80 group-hover:text-blue-100' => ! $item['active'],
                                        ]) />
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @else
                                    {{-- Route modul belum tersedia: tampilkan sebagai placeholder, bukan tautan. --}}
                                    <span
                                        class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-blue-200/50"
                                        aria-disabled="true"
                                        title="Modul belum tersedia"
                                    >
                                        <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                                        <span class="truncate">{{ $item['label'] }}</span>
                                        <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-200/70">
                                            Segera
                                        </span>
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </aside>
</div>
