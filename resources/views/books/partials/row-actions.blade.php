{{-- Tombol aksi per baris katalog. --}}
<div class="inline-flex items-center gap-1">
    <a href="{{ route('books.show', $book) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
        Detail<span class="sr-only"> {{ $book->title }}</span>
    </a>
    <a href="{{ route('books.edit', $book) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
        Edit<span class="sr-only"> {{ $book->title }}</span>
    </a>
    <button
        type="button"
        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
        x-data
        x-on:click="$dispatch('confirm-delete', { action: @js(route('books.destroy', $book)), name: @js($book->title) })"
    >
        Hapus<span class="sr-only"> {{ $book->title }}</span>
    </button>
</div>
