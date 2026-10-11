@extends('layouts.app')

@section('title', 'Katalog & Buku')

@section('content')
    <x-page-header
        title="Katalog & Buku"
        description="Kelola koleksi buku perpustakaan beserta penulis, kategori, dan penerbitnya."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog & Buku' => null]"
    >
        <x-slot:actions>
            <x-button :href="route('books.create')">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Buku
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Pencarian & filter -->
    <form method="GET" action="{{ route('books.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5" role="search" aria-label="Cari dan filter buku">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="md:col-span-2 xl:col-span-2">
                <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
                <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Judul atau ISBN" class="smpd-field pl-9">
                </div>
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-slate-700">Kategori</label>
                <select id="category" name="category" class="smpd-field mt-1.5">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($filters['category'] === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="author" class="block text-sm font-medium text-slate-700">Penulis</label>
                <select id="author" name="author" class="smpd-field mt-1.5">
                    <option value="">Semua penulis</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" @selected($filters['author'] === $author->id)>{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="publisher" class="block text-sm font-medium text-slate-700">Penerbit</label>
                <select id="publisher" name="publisher" class="smpd-field mt-1.5">
                    <option value="">Semua penerbit</option>
                    @foreach ($publishers as $publisher)
                        <option value="{{ $publisher->id }}" @selected($filters['publisher'] === $publisher->id)>{{ $publisher->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                <select id="status" name="status" class="smpd-field mt-1.5">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Book::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="sm:w-64">
                <label for="sort" class="block text-sm font-medium text-slate-700">Urutkan</label>
                <select id="sort" name="sort" class="smpd-field mt-1.5">
                    @foreach ($sorts as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                @if ($hasFilters || $filters['sort'] !== 'latest')
                    <x-button variant="secondary" :href="route('books.index')">Reset</x-button>
                @endif
                <x-button>
                    <x-icon name="search" class="h-4 w-4" />
                    Terapkan
                </x-button>
            </div>
        </div>
    </form>

    <!-- Hasil -->
    <section class="mt-6" aria-labelledby="results-heading">
        <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="results-heading" class="text-base font-semibold text-slate-900">Daftar Buku</h2>
            @if ($books->total() > 0)
                <p class="text-sm text-slate-500">
                    Menampilkan {{ $books->firstItem() }}–{{ $books->lastItem() }} dari {{ $books->total() }} buku
                </p>
            @endif
        </div>

        @if ($books->isEmpty())
            <div class="flex flex-col items-center rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-800">
                    <x-icon :name="$hasFilters ? 'search' : 'book'" class="h-6 w-6" />
                </span>
                @if ($hasFilters)
                    <p class="mt-4 text-sm font-semibold text-slate-900">Tidak ada buku yang cocok</p>
                    <p class="mt-1 max-w-sm text-sm text-slate-500">Coba ubah kata kunci atau filter yang digunakan.</p>
                    <x-button variant="secondary" :href="route('books.index')" class="mt-5">Reset pencarian</x-button>
                @else
                    <p class="mt-4 text-sm font-semibold text-slate-900">Belum ada buku di katalog</p>
                    <p class="mt-1 max-w-sm text-sm text-slate-500">Tambahkan buku pertama untuk mulai membangun koleksi perpustakaan.</p>
                    <x-button :href="route('books.create')" class="mt-5">
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Buku
                    </x-button>
                @endif
            </div>
        @else
            <!-- Tabel (tablet & desktop) -->
            <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white md:block">
                {{-- relative: agar teks sr-only di kolom aksi ikut terpotong oleh wadah scroll. --}}
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Buku</th>
                                <th scope="col" class="px-4 py-3">Penulis</th>
                                <th scope="col" class="px-4 py-3">Kategori<span class="xl:hidden"> / Tahun</span></th>
                                <th scope="col" class="hidden px-4 py-3 xl:table-cell">Penerbit</th>
                                <th scope="col" class="hidden px-4 py-3 xl:table-cell">Tahun</th>
                                <th scope="col" class="px-4 py-3">Stok</th>
                                <th scope="col" class="px-4 py-3 text-right"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($books as $book)
                                <tr class="align-top hover:bg-slate-50/60">
                                    <td class="px-4 py-3">
                                        <div class="flex items-start gap-3">
                                            @include('books.partials.cover', ['book' => $book, 'size' => 'sm'])
                                            <div class="min-w-0 max-w-xs">
                                                <a href="{{ route('books.show', $book) }}" class="line-clamp-2 font-semibold text-slate-900 hover:text-blue-800 hover:underline">{{ $book->title }}</a>
                                                <p class="mt-0.5 text-xs text-slate-500">{{ $book->isbn ? 'ISBN '.$book->isbn : 'Tanpa ISBN' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="max-w-[12rem] px-4 py-3 text-slate-600">
                                        <span class="line-clamp-2">{{ $book->authors->pluck('name')->join(', ') ?: '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $book->category?->name ?? '-' }}
                                        <span class="mt-0.5 block text-xs text-slate-500 xl:hidden">{{ $book->publication_year }}</span>
                                    </td>
                                    <td class="hidden max-w-[10rem] px-4 py-3 text-slate-600 xl:table-cell"><span class="line-clamp-2">{{ $book->publisher?->name ?? '-' }}</span></td>
                                    <td class="hidden px-4 py-3 text-slate-600 xl:table-cell">{{ $book->publication_year }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-slate-900">{{ $book->stock }}</span>
                                        <span @class([
                                            'mt-1 block w-max rounded-full px-2 py-0.5 text-[11px] font-medium',
                                            'bg-green-50 text-green-700' => $book->status === 'available',
                                            'bg-slate-100 text-slate-600' => $book->status !== 'available',
                                        ])>{{ $book->status_label }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        @include('books.partials.row-actions', ['book' => $book])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kartu (mobile) -->
            <ul class="space-y-3 md:hidden">
                @foreach ($books as $book)
                    <li class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start gap-3">
                            @include('books.partials.cover', ['book' => $book, 'size' => 'sm'])
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('books.show', $book) }}" class="line-clamp-2 font-semibold text-slate-900 hover:text-blue-800 hover:underline">{{ $book->title }}</a>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $book->authors->pluck('name')->join(', ') ?: '-' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $book->category?->name ?? '-' }} · {{ $book->publication_year }}</p>
                                <p class="mt-1 text-xs text-slate-600">
                                    Stok <span class="font-semibold text-slate-900">{{ $book->stock }}</span> · {{ $book->status_label }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 border-t border-slate-100 pt-3 text-right">
                            @include('books.partials.row-actions', ['book' => $book])
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">
                {{ $books->links() }}
            </div>
        @endif
    </section>

    <x-confirm-delete title="Hapus buku?" description="Relasi penulis buku ini ikut dilepas, tetapi data penulis, kategori, dan penerbit tetap tersimpan." />
@endsection
