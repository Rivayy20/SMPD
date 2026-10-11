<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /**
     * Nilai kolom status (enum di migration) beserta labelnya.
     */
    public const STATUSES = [
        'available' => 'Tersedia',
        'unavailable' => 'Tidak tersedia',
    ];

    /**
     * Disk dan folder penyimpanan sampul buku.
     */
    public const COVER_DISK = 'public';

    public const COVER_DIRECTORY = 'covers';

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'category_id',
        'publisher_id',
        'publication_year',
        'stock',
        'cover_image',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function authors()
    {
        // Pivot: book_authors (book_id, author_id), tanpa timestamps.
        return $this->belongsToMany(Author::class, 'book_authors');
    }

    /**
     * URL publik sampul buku, atau null jika belum ada sampul.
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_image
            ? Storage::disk(self::COVER_DISK)->url($this->cover_image)
            : null);
    }

    /**
     * Label status dalam bahasa Indonesia.
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    /**
     * Cari berdasarkan judul atau ISBN.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('isbn', 'like', "%{$term}%");
        });
    }

    /**
     * Filter katalog. Nilai kosong diabaikan.
     *
     * @param  array{category?: int|null, author?: int|null, publisher?: int|null, status?: string|null}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['category'] ?? null, fn (Builder $query, int $id) => $query->where('category_id', $id))
            ->when($filters['publisher'] ?? null, fn (Builder $query, int $id) => $query->where('publisher_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['author'] ?? null, fn (Builder $query, int $id) => $query->whereHas(
                'authors',
                fn (Builder $query) => $query->whereKey($id),
            ));
    }
}
