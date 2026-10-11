@extends('layouts.app')

@section('title', $book->title)

@section('content')
    <x-page-header
        :title="$book->title"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog & Buku' => route('books.index'), 'Detail Buku' => null]"
    >
        <x-slot:actions>
            <x-button variant="secondary" :href="route('books.index')">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali
            </x-button>
            <x-button variant="secondary" :href="route('books.edit', $book)">
                <x-icon name="pencil" class="h-4 w-4" />
                Edit
            </x-button>
            <x-button
                variant="danger"
                type="button"
                x-data
                {{-- Di atribut komponen <x-...>, gunakan {{ Js::from() }}; direktif @js tidak dikompilasi di sana. --}}
                x-on:click="$dispatch('confirm-delete', { action: {{ \Illuminate\Support\Js::from(route('books.destroy', $book)) }}, name: {{ \Illuminate\Support\Js::from($book->title) }} })"
            >
                <x-icon name="trash" class="h-4 w-4" />
                Hapus
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
        <div class="mx-auto w-full max-w-[16rem]">
            @include('books.partials.cover', ['book' => $book, 'size' => 'lg'])
        </div>

        <div class="space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="info-heading">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="info-heading" class="text-base font-semibold text-slate-900">Informasi Buku</h2>
                    <span @class([
                        'rounded-full px-2.5 py-1 text-xs font-medium',
                        'bg-green-50 text-green-700' => $book->status === 'available',
                        'bg-slate-100 text-slate-600' => $book->status !== 'available',
                    ])>{{ $book->status_label }}</span>
                </div>

                <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Penulis</dt>
                        <dd class="mt-1 text-sm text-slate-900">
                            @forelse ($book->authors as $author)
                                <span class="mb-1 mr-1 inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-800">{{ $author->name }}</span>
                            @empty
                                <span class="text-slate-500">Belum ada penulis</span>
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">ISBN</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $book->isbn ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tahun Terbit</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $book->publication_year }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Kategori</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $book->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Penerbit</dt>
                        <dd class="mt-1 text-sm text-slate-900">
                            {{ $book->publisher?->name ?? '-' }}
                            @if ($book->publisher)
                                <span class="text-slate-500">({{ $book->publisher->type === 'distributor' ? 'Distributor' : 'Penerbit' }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Stok</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ number_format($book->stock, 0, ',', '.') }} eksemplar</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Terakhir Diperbarui</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $book->updated_at?->translatedFormat('d F Y') ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="description-heading">
                <h2 id="description-heading" class="text-base font-semibold text-slate-900">Deskripsi</h2>
                @if ($book->description)
                    <p class="mt-3 whitespace-pre-line break-words text-sm leading-relaxed text-slate-700">{{ $book->description }}</p>
                @else
                    <p class="mt-3 text-sm text-slate-500">Belum ada deskripsi untuk buku ini.</p>
                @endif
            </section>
        </div>
    </div>

    <x-confirm-delete title="Hapus buku?" description="Relasi penulis buku ini ikut dilepas, tetapi data penulis, kategori, dan penerbit tetap tersimpan." />
@endsection
