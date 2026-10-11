<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class BookController extends Controller
{
    /**
     * Pilihan urutan katalog: nilai parameter 'sort' => [kolom, arah].
     */
    private const SORTS = [
        'latest' => ['created_at', 'desc'],
        'title_asc' => ['title', 'asc'],
        'title_desc' => ['title', 'desc'],
        'year_desc' => ['publication_year', 'desc'],
        'year_asc' => ['publication_year', 'asc'],
        'stock_desc' => ['stock', 'desc'],
        'stock_asc' => ['stock', 'asc'],
    ];

    private const PER_PAGE = 10;

    public function index(Request $request): View
    {
        // Nilai filter yang tidak valid (bukan angka, bukan pilihan yang dikenal, atau berupa array) diabaikan.
        $id = function (string $key) use ($request): ?int {
            $value = $request->query($key);

            return is_string($value) && ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
        };

        $option = function (string $key, array $options) use ($request): ?string {
            $value = $request->query($key);

            return is_string($value) && array_key_exists($value, $options) ? $value : null;
        };

        $filters = [
            'q' => is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 100) : '',
            'category' => $id('category'),
            'author' => $id('author'),
            'publisher' => $id('publisher'),
            'status' => $option('status', Book::STATUSES),
            'sort' => $option('sort', self::SORTS) ?? 'latest',
        ];

        [$column, $direction] = self::SORTS[$filters['sort']];

        $books = Book::query()
            ->with(['category:id,name', 'publisher:id,name', 'authors:id,name'])
            ->search($filters['q'])
            ->filter($filters)
            ->orderBy($column, $direction)
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('books.index', [
            'books' => $books,
            'filters' => $filters,
            'hasFilters' => $filters['q'] !== '' || $filters['category'] || $filters['author'] || $filters['publisher'] || $filters['status'],
            'sorts' => [
                'latest' => 'Terbaru ditambahkan',
                'title_asc' => 'Judul A–Z',
                'title_desc' => 'Judul Z–A',
                'year_desc' => 'Tahun terbit terbaru',
                'year_asc' => 'Tahun terbit terlama',
                'stock_desc' => 'Stok terbanyak',
                'stock_asc' => 'Stok paling sedikit',
            ],
            ...$this->referenceData(),
        ]);
    }

    public function create(): View
    {
        return view('books.create', [
            'book' => new Book(['status' => 'available', 'stock' => 0]),
            ...$this->referenceData(),
        ]);
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $coverPath = $this->storeCover($request->file('cover_image'));

        try {
            $book = DB::transaction(function () use ($request, $coverPath) {
                $book = Book::create([
                    ...$request->safe()->except(['authors', 'cover_image']),
                    'cover_image' => $coverPath,
                ]);

                $book->authors()->sync($request->validated('authors'));

                return $book;
            });
        } catch (Throwable $exception) {
            $this->deleteCover($coverPath);
            report($exception);

            return back()->withInput()->with('error', 'Buku gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('books.show', $book)
            ->with('success', "Buku \"{$book->title}\" berhasil ditambahkan.");
    }

    public function show(Book $book): View
    {
        $book->load(['category', 'publisher', 'authors' => fn ($query) => $query->orderBy('name')]);

        return view('books.show', ['book' => $book]);
    }

    public function edit(Book $book): View
    {
        $book->load('authors:id');

        return view('books.edit', [
            'book' => $book,
            ...$this->referenceData(),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $oldCover = $book->cover_image;
        $newCover = $this->storeCover($request->file('cover_image'));
        $removeCover = ! $newCover && $request->boolean('remove_cover');

        try {
            DB::transaction(function () use ($request, $book, $oldCover, $newCover, $removeCover) {
                $book->update([
                    ...$request->safe()->except(['authors', 'cover_image', 'remove_cover']),
                    'cover_image' => $newCover ?? ($removeCover ? null : $oldCover),
                ]);

                $book->authors()->sync($request->validated('authors'));
            });
        } catch (Throwable $exception) {
            $this->deleteCover($newCover);
            report($exception);

            return back()->withInput()->with('error', 'Perubahan buku gagal disimpan. Silakan coba lagi.');
        }

        // File lama baru dihapus setelah database berhasil diperbarui.
        if ($newCover || $removeCover) {
            $this->deleteCover($oldCover);
        }

        return redirect()
            ->route('books.show', $book)
            ->with('success', "Buku \"{$book->title}\" berhasil diperbarui.");
    }

    public function destroy(Book $book): RedirectResponse
    {
        $cover = $book->cover_image;
        $title = $book->title;

        try {
            DB::transaction(function () use ($book) {
                // Relasi penulis dilepas eksplisit; data penulis sendiri tidak dihapus.
                $book->authors()->detach();
                $book->delete();
            });
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', "Buku \"{$title}\" tidak dapat dihapus karena masih digunakan oleh data lain.");
        }

        $this->deleteCover($cover);

        return redirect()
            ->route('books.index')
            ->with('success', "Buku \"{$title}\" berhasil dihapus.");
    }

    /**
     * Data pilihan untuk filter dan form (kategori, penerbit, penulis).
     *
     * @return array<string, Collection<int, Model>>
     */
    private function referenceData(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'publishers' => Publisher::query()->orderBy('name')->get(['id', 'name', 'type']),
            'authors' => Author::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function storeCover(?UploadedFile $file): ?string
    {
        // Nama file dibuat acak oleh Laravel, bukan dari input pengguna.
        return $file?->store(Book::COVER_DIRECTORY, Book::COVER_DISK) ?: null;
    }

    private function deleteCover(?string $path): void
    {
        if ($path) {
            Storage::disk(Book::COVER_DISK)->delete($path);
        }
    }
}
