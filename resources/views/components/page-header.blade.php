{{--
    Header konten halaman: breadcrumb, judul, deskripsi, dan tombol aksi.

    <x-page-header
        title="Katalog Buku"
        description="Kelola seluruh koleksi buku."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog Buku' => null]"
    >
        <x-slot:actions>
            <a href="..." class="...">Tambah Buku</a>
        </x-slot:actions>
    </x-page-header>
--}}
@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    @if ($breadcrumbs)
        <nav aria-label="Breadcrumb" class="mb-2">
            <ol class="flex flex-wrap items-center gap-1 text-sm text-slate-500">
                @foreach ($breadcrumbs as $label => $url)
                    <li class="flex items-center gap-1">
                        @unless ($loop->first)
                            <x-icon name="chevron-right" class="h-3.5 w-3.5 text-slate-400" />
                        @endunless

                        @if ($url && ! $loop->last)
                            <a href="{{ $url }}" class="hover:text-blue-800 hover:underline">{{ $label }}</a>
                        @else
                            <span @if ($loop->last) aria-current="page" class="font-medium text-slate-700" @endif>{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>

            @if ($description)
                <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
