{{-- Sampul buku dengan fallback ikon. $size: sm | lg --}}
@php($size = $size ?? 'sm')

@if ($book->cover_url)
    <img
        src="{{ $book->cover_url }}"
        alt="Sampul buku {{ $book->title }}"
        loading="lazy"
        @class([
            'shrink-0 rounded-md border border-slate-200 bg-slate-100',
            'h-16 w-12 object-cover' => $size === 'sm',
            'aspect-[3/4] w-full object-contain' => $size === 'lg',
        ])
    >
@else
    <div
        @class([
            'flex shrink-0 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-300',
            'h-16 w-12' => $size === 'sm',
            'aspect-[3/4] w-full flex-col gap-2' => $size === 'lg',
        ])
        role="img"
        aria-label="Sampul belum tersedia"
    >
        <x-icon name="book" @class(['h-6 w-6' => $size === 'sm', 'h-12 w-12' => $size === 'lg']) />
        @if ($size === 'lg')
            <span class="text-xs font-medium text-slate-400">Sampul belum tersedia</span>
        @endif
    </div>
@endif
