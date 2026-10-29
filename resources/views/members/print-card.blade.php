<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak ID Card - {{ $member->name }} · {{ config('app.name', 'SMPD') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased p-4 sm:p-8">

    <!-- Toolbar Atas (Tidak ikut dicetak) -->
    <div class="no-print mx-auto mb-6 flex max-w-2xl items-center justify-between rounded-xl border border-slate-200 bg-white px-5 py-3 shadow-xs">
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-900">
                <x-icon name="printer" class="h-4 w-4" />
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-900">Cetak ID Card Anggota</p>
                <p class="text-xs text-slate-500">{{ $member->name }} ({{ $member->membership_number }})</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('members.show', $member) }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
            >
                Kembali
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-900 px-4 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-blue-800 focus:outline-none"
            >
                <x-icon name="printer" class="h-3.5 w-3.5" />
                <span>Cetak Sekarang</span>
            </button>
        </div>
    </div>

    <!-- Container ID Card (Siap Cetak) -->
    <div class="print-container mx-auto max-w-md space-y-6">

        <!-- SISI DEPAN (FRONT) -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 via-blue-900 to-indigo-950 p-6 text-white shadow-xl ring-1 ring-slate-300/50" style="aspect-ratio: 1.586 / 1;">
            <!-- Watermark Pattern -->
            <div class="pointer-events-none absolute -bottom-8 -right-8 opacity-10">
                <x-application-logo class="h-48 w-48 text-white" />
            </div>

            <!-- Header Kartu -->
            <div class="flex items-center justify-between border-b border-white/20 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-400/20 text-sky-300 ring-1 ring-sky-300/30">
                        <x-application-logo class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-xs font-extrabold tracking-wider text-white">{{ config('app.name', 'SMPD') }}</p>
                        <p class="text-[10px] text-blue-200">KARTU ANGGOTA PERPUSTAKAAN</p>
                    </div>
                </div>
                <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-semibold text-sky-200">
                    {{ strtoupper($member->membershipType?->name ?? 'REGULER') }}
                </span>
            </div>

            <!-- Konten Utama Kartu -->
            <div class="mt-4 flex items-center gap-4">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-white/15 text-2xl font-bold text-white shadow-inner ring-2 ring-white/20">
                    {{ strtoupper(substr($member->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-bold text-white">{{ $member->name }}</p>
                    <p class="font-mono text-xs text-sky-300">ID: {{ $member->membership_number }}</p>
                    <p class="mt-0.5 text-[11px] text-blue-200/80">
                        Mulai: {{ $member->join_date ? \Carbon\Carbon::parse($member->join_date)->translatedFormat('d M Y') : '—' }}
                    </p>
                </div>
            </div>

            <!-- Barcode & Nomor Kartu -->
            <div class="absolute inset-x-6 bottom-4 rounded-xl bg-white/10 p-2.5 backdrop-blur-xs">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="font-mono font-bold tracking-wider text-white">
                        {{ $member->card_number ?? $member->membership_number }}
                    </span>
                    <span class="text-[10px] uppercase tracking-wider text-blue-200">PERPUSTAKAAN DIGITAL</span>
                </div>

                <!-- Barcode Faux -->
                <div class="mt-1.5 flex h-5 w-full items-center justify-center space-x-1 rounded bg-white px-2 py-0.5">
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

        <!-- SISI BELAKANG (BACK) -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-300 bg-white p-6 text-slate-800 shadow-sm" style="aspect-ratio: 1.586 / 1;">
            <div class="border-b border-slate-200 pb-2">
                <p class="text-xs font-bold uppercase tracking-wider text-blue-950">Ketentuan Penggunaan Kartu</p>
            </div>

            <ol class="mt-3 list-decimal space-y-1 pl-4 text-[11px] leading-relaxed text-slate-600">
                <li>Kartu ini adalah bukti keanggotaan sah di perpustakaan digital.</li>
                <li>Wajib dibawa saat berkunjung, meminjam, atau mengembalikan buku.</li>
                <li>Tidak boleh dipindahtangankan kepada orang lain tanpa izin petugas.</li>
                <li>Jika kartu hilang atau rusak, segera lapor ke admin perpustakaan.</li>
            </ol>

            <div class="absolute inset-x-6 bottom-4 flex items-end justify-between border-t border-slate-100 pt-2 text-[10px] text-slate-500">
                <div>
                    <p class="font-semibold text-slate-700">{{ config('app.name', 'SMPD') }} Perpustakaan</p>
                    <p>Sistem Manajemen Perpustakaan Digital</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] uppercase tracking-wider text-slate-400">Petugas Perpustakaan</p>
                    <p class="mt-4 font-semibold text-slate-700">Tanda Tangan / Stempel</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
