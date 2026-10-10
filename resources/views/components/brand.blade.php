{{--
    Identitas SMPD (logo + nama) untuk halaman publik dan autentikasi.
    <x-brand />                    → di atas latar terang
    <x-brand dark />               → di atas latar navy (sama seperti sidebar)
    <x-brand subtitle="..." />     → ganti teks kecil di bawah nama
--}}
@props([
    'dark' => false,
    'subtitle' => 'Perpustakaan Digital',
])

<span {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-3']) }}>
    <span @class([
        'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
        'bg-sky-500/15 text-sky-300' => $dark,
        'bg-blue-900 text-white' => ! $dark,
    ])>
        <x-application-logo class="h-5 w-5" />
    </span>
    <span class="min-w-0 leading-tight">
        <span @class([
            'block text-base font-bold tracking-wide',
            'text-white' => $dark,
            'text-slate-900' => ! $dark,
        ])>{{ config('app.name', 'SMPD') }}</span>
        @if ($subtitle)
            <span @class([
                'block truncate text-xs',
                'text-blue-200/80' => $dark,
                'text-slate-500' => ! $dark,
            ])>{{ $subtitle }}</span>
        @endif
    </span>
</span>
