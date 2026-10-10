{{--
    Kartu statistik ringkas.
    <x-stat-card label="Total Buku" :value="$stats['books']" icon="book" hint="120 eksemplar" />
--}}
@props([
    'label',
    'value',
    'icon' => 'archive',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-5']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($value, 0, ',', '.') }}</p>
        </div>
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
            <x-icon :name="$icon" class="h-6 w-6" />
        </span>
    </div>

    @if ($hint)
        <p class="mt-3 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
