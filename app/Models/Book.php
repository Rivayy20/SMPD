<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

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
}
