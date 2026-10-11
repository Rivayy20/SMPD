{{--
    Tombol SMPD. Otomatis menjadi <a> jika diberi href.

    <x-button>Simpan</x-button>
    <x-button variant="secondary" :href="route('books.index')">Batal</x-button>
    <x-button variant="danger" type="button">Hapus</x-button>

    variant: primary (default) | secondary | danger
    size:    md (default) | sm
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
        'px-4 py-2.5 text-sm' => $size === 'md',
        'px-3 py-1.5 text-xs' => $size === 'sm',
        'bg-blue-900 text-white shadow-sm hover:bg-blue-800 focus-visible:ring-blue-600' => $variant === 'primary',
        'border border-slate-300 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50 focus-visible:ring-blue-600' => $variant === 'secondary',
        'bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:ring-red-500' => $variant === 'danger',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
