{{--
    Form bersama tambah & edit buku.
    Variabel: $book, $categories, $publishers, $authors, $action, $method ('POST' | 'PUT'), $submitLabel
--}}
@php
    $selectedAuthors = collect(old('authors', $book->exists ? $book->authors->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $missingReferences = collect([
        'kategori' => $categories->isEmpty(),
        'penerbit' => $publishers->isEmpty(),
        'penulis' => $authors->isEmpty(),
    ])->filter()->keys();
    $field = fn (string $name) => $errors->has($name) ? 'smpd-field smpd-field-invalid' : 'smpd-field';
@endphp

@if ($missingReferences->isNotEmpty())
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
        <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0" />
        <p>
            Data {{ $missingReferences->join(', ', ' dan ') }} belum tersedia, sehingga buku belum dapat disimpan.
            Data referensi ini akan dikelola melalui modulnya masing-masing.
        </p>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        Periksa kembali data yang ditandai di bawah ini.
    </div>
@endif

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <!-- Informasi utama -->
    <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="section-main">
        <h2 id="section-main" class="text-base font-semibold text-slate-900">Informasi Buku</h2>

        <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="title" class="block text-sm font-medium text-slate-700">Judul <span class="text-red-600">*</span></label>
                <input id="title" name="title" type="text" value="{{ old('title', $book->title) }}" required maxlength="255"
                    class="{{ $field('title') }} mt-1.5" @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>
                <x-input-error id="title-error" :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <label for="isbn" class="block text-sm font-medium text-slate-700">ISBN</label>
                <input id="isbn" name="isbn" type="text" value="{{ old('isbn', $book->isbn) }}" maxlength="20" placeholder="978-602-..."
                    class="{{ $field('isbn') }} mt-1.5" aria-describedby="isbn-help @error('isbn') isbn-error @enderror" @error('isbn') aria-invalid="true" @enderror>
                <p id="isbn-help" class="mt-1.5 text-xs text-slate-500">Opsional. Angka, tanda hubung, dan huruf X; harus unik.</p>
                <x-input-error id="isbn-error" :messages="$errors->get('isbn')" class="mt-2" />
            </div>

            <div>
                <label for="publication_year" class="block text-sm font-medium text-slate-700">Tahun Terbit <span class="text-red-600">*</span></label>
                <input id="publication_year" name="publication_year" type="number" inputmode="numeric" value="{{ old('publication_year', $book->publication_year) }}"
                    min="1000" max="{{ now()->year + 1 }}" required
                    class="{{ $field('publication_year') }} mt-1.5" @error('publication_year') aria-invalid="true" aria-describedby="publication_year-error" @enderror>
                <x-input-error id="publication_year-error" :messages="$errors->get('publication_year')" class="mt-2" />
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-slate-700">Kategori <span class="text-red-600">*</span></label>
                <select id="category_id" name="category_id" required class="{{ $field('category_id') }} mt-1.5"
                    @error('category_id') aria-invalid="true" aria-describedby="category_id-error" @enderror>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $book->category_id) === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error id="category_id-error" :messages="$errors->get('category_id')" class="mt-2" />
            </div>

            <div>
                <label for="publisher_id" class="block text-sm font-medium text-slate-700">Penerbit <span class="text-red-600">*</span></label>
                <select id="publisher_id" name="publisher_id" required class="{{ $field('publisher_id') }} mt-1.5"
                    @error('publisher_id') aria-invalid="true" aria-describedby="publisher_id-error" @enderror>
                    <option value="">Pilih penerbit</option>
                    @foreach ($publishers as $publisher)
                        <option value="{{ $publisher->id }}" @selected((int) old('publisher_id', $book->publisher_id) === $publisher->id)>
                            {{ $publisher->name }}{{ $publisher->type === 'distributor' ? ' (Distributor)' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-input-error id="publisher_id-error" :messages="$errors->get('publisher_id')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea id="description" name="description" rows="5" maxlength="5000" placeholder="Sinopsis atau catatan singkat tentang buku"
                    class="{{ $field('description') }} mt-1.5" @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $book->description) }}</textarea>
                <x-input-error id="description-error" :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </section>

    <!-- Penulis -->
    <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="section-authors"
        x-data="{ search: '', selected: @js($selectedAuthors) }">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="section-authors" class="text-base font-semibold text-slate-900">Penulis <span class="text-red-600">*</span></h2>
            <p class="text-xs text-slate-500" aria-live="polite"><span x-text="selected.length">{{ count($selectedAuthors) }}</span> dipilih</p>
        </div>
        <p class="mt-1 text-sm text-slate-500">Pilih satu atau lebih penulis buku.</p>

        @if ($authors->isNotEmpty())
            <label for="author-search" class="sr-only">Cari penulis</label>
            <input id="author-search" type="search" x-model="search" placeholder="Cari nama penulis..." class="smpd-field mt-4">

            <fieldset class="mt-3">
                <legend class="sr-only">Daftar penulis</legend>
                <div @class([
                    'max-h-64 overflow-y-auto rounded-lg border p-2',
                    'border-red-400' => $errors->has('authors') || $errors->has('authors.*'),
                    'border-slate-200' => ! ($errors->has('authors') || $errors->has('authors.*')),
                ])>
                    @foreach ($authors as $author)
                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 text-sm text-slate-700 hover:bg-slate-50"
                            x-show="search === '' || @js(mb_strtolower($author->name)).includes(search.toLowerCase())"
                        >
                            <input type="checkbox" name="authors[]" value="{{ $author->id }}" x-model.number="selected"
                                class="h-4 w-4 rounded border-slate-300 text-blue-800 focus:ring-2 focus:ring-blue-600"
                                @checked(in_array($author->id, $selectedAuthors, true))>
                            <span class="min-w-0 break-words">{{ $author->name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <x-input-error :messages="collect($errors->get('authors'))->merge(collect($errors->get('authors.*'))->flatten())->unique()->all()" class="mt-2" />
    </section>

    <!-- Stok & status -->
    <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="section-stock">
        <h2 id="section-stock" class="text-base font-semibold text-slate-900">Stok & Status</h2>

        <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="stock" class="block text-sm font-medium text-slate-700">Stok (eksemplar) <span class="text-red-600">*</span></label>
                <input id="stock" name="stock" type="number" inputmode="numeric" min="0" max="99999" value="{{ old('stock', $book->stock) }}" required
                    class="{{ $field('stock') }} mt-1.5" @error('stock') aria-invalid="true" aria-describedby="stock-error" @enderror>
                <x-input-error id="stock-error" :messages="$errors->get('stock')" class="mt-2" />
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-600">*</span></label>
                <select id="status" name="status" required class="{{ $field('status') }} mt-1.5"
                    @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
                    @foreach (\App\Models\Book::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $book->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error id="status-error" :messages="$errors->get('status')" class="mt-2" />
            </div>
        </div>
    </section>

    <!-- Sampul -->
    <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="section-cover">
        <h2 id="section-cover" class="text-base font-semibold text-slate-900">Sampul</h2>

        <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-start">
            <div class="w-28 shrink-0">
                @include('books.partials.cover', ['book' => $book, 'size' => 'lg'])
            </div>

            <div class="min-w-0 flex-1 space-y-4">
                <div>
                    <label for="cover_image" class="block text-sm font-medium text-slate-700">
                        {{ $book->cover_image ? 'Ganti sampul' : 'Unggah sampul' }}
                    </label>
                    <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp"
                        class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-800 hover:file:bg-blue-100"
                        aria-describedby="cover-help @error('cover_image') cover_image-error @enderror">
                    <p id="cover-help" class="mt-1.5 text-xs text-slate-500">
                        Opsional. Format JPG, PNG, atau WEBP, maksimal 2 MB.
                        @if ($book->cover_image) Kosongkan untuk mempertahankan sampul saat ini. @endif
                    </p>
                    <x-input-error id="cover_image-error" :messages="$errors->get('cover_image')" class="mt-2" />
                </div>

                @if ($book->cover_image)
                    <label for="remove_cover" class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input id="remove_cover" type="checkbox" name="remove_cover" value="1" @checked(old('remove_cover'))
                            class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-2 focus:ring-red-500">
                        Hapus sampul saat ini
                    </label>
                @endif
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <x-button variant="secondary" :href="$book->exists ? route('books.show', $book) : route('books.index')">Batal</x-button>
        <x-button :disabled="$missingReferences->isNotEmpty()">{{ $submitLabel }}</x-button>
    </div>
</form>
