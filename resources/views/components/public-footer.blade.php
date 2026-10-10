{{--
    Footer halaman publik.
--}}
<footer class="bg-blue-950 text-blue-100">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
            <div class="max-w-md">
                <x-brand dark subtitle="Sistem Manajemen Perpustakaan Digital" />
                <p class="mt-4 text-sm leading-relaxed text-blue-200/80">
                    Sistem informasi untuk membantu petugas mengelola data perpustakaan secara terstruktur
                    dalam satu tempat.
                </p>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs text-blue-200/70 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'SMPD') }}. Hak cipta dilindungi.</p>
            <p>Digunakan untuk pengelolaan perpustakaan digital.</p>
        </div>
    </div>
</footer>
