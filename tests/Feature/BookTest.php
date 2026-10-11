<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Book::COVER_DISK);
        $this->petugas = User::factory()->petugas()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Akses & otorisasi
    |--------------------------------------------------------------------------
    */

    public function test_guests_are_redirected_to_login(): void
    {
        $book = Book::factory()->create();

        foreach (['/books', '/books/create', "/books/{$book->id}", "/books/{$book->id}/edit"] as $uri) {
            $this->get($uri)->assertRedirect(route('login', absolute: false));
        }

        $this->post('/books', $this->validPayload())->assertRedirect(route('login', absolute: false));
        $this->delete("/books/{$book->id}")->assertRedirect(route('login', absolute: false));
        $this->assertModelExists($book);
    }

    public function test_admin_and_petugas_can_open_the_catalog(): void
    {
        foreach ([User::factory()->admin()->create(), $this->petugas] as $user) {
            $this->actingAs($user)->get('/books')->assertOk()->assertViewIs('books.index');
        }
    }

    public function test_users_with_other_roles_are_forbidden(): void
    {
        // Role di luar admin/petugas (mis. role baru di masa depan) harus ditolak di server.
        $guest = User::factory()->make(['id' => 999, 'role' => 'anggota']);
        $book = Book::factory()->create();

        $this->actingAs($guest)->get('/books')->assertForbidden();
        $this->actingAs($guest)->post('/books', $this->validPayload())->assertForbidden();
        $this->actingAs($guest)->delete("/books/{$book->id}")->assertForbidden();

        $this->assertModelExists($book);
        $this->assertDatabaseCount('books', 1);
    }

    public function test_sidebar_marks_catalog_menu_as_active(): void
    {
        $book = Book::factory()->create();

        foreach (['/books', "/books/{$book->id}"] as $uri) {
            $this->actingAs($this->petugas)->get($uri)
                ->assertSee('href="'.route('books.index').'"', false)
                ->assertSee('aria-current="page"', false);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Katalog: daftar, pencarian, filter, pagination
    |--------------------------------------------------------------------------
    */

    public function test_catalog_shows_empty_state_when_there_are_no_books(): void
    {
        $this->actingAs($this->petugas)->get('/books')
            ->assertOk()
            ->assertSee('Belum ada buku di katalog');
    }

    public function test_catalog_lists_books_with_their_relations(): void
    {
        $book = Book::factory()
            ->hasAttached(Author::factory()->state(['name' => 'Andrea Hirata']))
            ->create(['title' => 'Laskar Pelangi', 'isbn' => '9789793062792']);

        $this->actingAs($this->petugas)->get('/books')
            ->assertOk()
            ->assertSee('Laskar Pelangi')
            ->assertSee('9789793062792')
            ->assertSee('Andrea Hirata')
            ->assertSee($book->category->name)
            ->assertSee($book->publisher->name);
    }

    public function test_catalog_does_not_run_extra_queries_per_book(): void
    {
        $countQueries = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->petugas)->get('/books')->assertOk();

            return count(DB::getQueryLog());
        };

        Book::factory()->count(2)->hasAttached(Author::factory()->count(2))->create();
        $fewBooks = $countQueries();

        Book::factory()->count(8)->hasAttached(Author::factory()->count(2))->create();
        $manyBooks = $countQueries();

        $this->assertSame($fewBooks, $manyBooks);
    }

    public function test_catalog_can_be_searched_by_title_or_isbn(): void
    {
        Book::factory()->create(['title' => 'Bumi Manusia', 'isbn' => '9789799731234']);
        Book::factory()->create(['title' => 'Ronggeng Dukuh Paruk', 'isbn' => '9789792201000']);

        $this->actingAs($this->petugas)->get('/books?q=bumi')
            ->assertSee('Bumi Manusia')
            ->assertDontSee('Ronggeng Dukuh Paruk');

        $this->actingAs($this->petugas)->get('/books?q=9789792201')
            ->assertSee('Ronggeng Dukuh Paruk')
            ->assertDontSee('Bumi Manusia');

        $this->actingAs($this->petugas)->get('/books?q=tidak-ada-judul-ini')
            ->assertSee('Tidak ada buku yang cocok');
    }

    public function test_catalog_can_be_filtered_by_category_author_publisher_and_status(): void
    {
        $fiksi = Category::factory()->create(['name' => 'Fiksi']);
        $gramedia = Publisher::factory()->create(['name' => 'Gramedia']);
        $andrea = Author::factory()->create();

        $match = Book::factory()->for($fiksi)->for($gramedia)->hasAttached($andrea)->create(['title' => 'Buku Cocok', 'status' => 'available']);
        Book::factory()->create(['title' => 'Buku Lain Kategori']);
        Book::factory()->for($fiksi)->for($gramedia)->create(['title' => 'Buku Tanpa Penulis Itu']);
        Book::factory()->for($fiksi)->for($gramedia)->hasAttached($andrea)->unavailable()->create(['title' => 'Buku Tidak Tersedia']);

        $this->actingAs($this->petugas)
            ->get("/books?category={$fiksi->id}&publisher={$gramedia->id}&author={$andrea->id}&status=available")
            ->assertOk()
            ->assertViewHas('books', fn ($books) => $books->pluck('id')->all() === [$match->id]);
    }

    public function test_catalog_paginates_and_keeps_filters_in_page_links(): void
    {
        $category = Category::factory()->create();
        Book::factory()->count(12)->for($category)->sequence(fn ($sequence) => ['title' => 'Novel '.str_pad($sequence->index, 2, '0', STR_PAD_LEFT)])->create();
        Book::factory()->count(3)->create(['title' => 'Bukan Novel']);

        $response = $this->actingAs($this->petugas)->get("/books?q=Novel&category={$category->id}&sort=title_asc");

        $response->assertOk()
            ->assertViewHas('books', fn ($books) => $books->total() === 12 && $books->count() === 10)
            ->assertSee('Novel 00')
            ->assertDontSee('Novel 11')
            ->assertSee('page=2', false)
            ->assertSee('q=Novel', false)
            ->assertSee("category={$category->id}", false);

        $this->actingAs($this->petugas)->get("/books?q=Novel&category={$category->id}&sort=title_asc&page=2")
            ->assertSee('Novel 11')
            ->assertDontSee('Novel 00')
            ->assertDontSee('Bukan Novel');
    }

    public function test_catalog_can_be_sorted(): void
    {
        Book::factory()->create(['title' => 'Lama', 'publication_year' => 1990]);
        Book::factory()->create(['title' => 'Baru', 'publication_year' => 2024]);

        $this->actingAs($this->petugas)->get('/books?sort=year_asc')
            ->assertViewHas('books', fn ($books) => $books->pluck('title')->all() === ['Lama', 'Baru']);

        $this->actingAs($this->petugas)->get('/books?sort=year_desc')
            ->assertViewHas('books', fn ($books) => $books->pluck('title')->all() === ['Baru', 'Lama']);
    }

    public function test_invalid_catalog_parameters_are_ignored(): void
    {
        Book::factory()->create(['title' => 'Tetap Tampil']);

        $this->actingAs($this->petugas)
            ->get('/books?category[]=1&author=abc&publisher=-5&status=hilang&sort=id;drop&q[]=x')
            ->assertOk()
            ->assertSee('Tetap Tampil')
            ->assertViewHas('filters', fn ($filters) => $filters['category'] === null
                && $filters['author'] === null
                && $filters['publisher'] === null
                && $filters['status'] === null
                && $filters['sort'] === 'latest'
                && $filters['q'] === '');
    }

    /*
    |--------------------------------------------------------------------------
    | Detail
    |--------------------------------------------------------------------------
    */

    public function test_book_detail_shows_all_information(): void
    {
        $book = Book::factory()
            ->hasAttached(Author::factory()->count(2))
            ->create(['description' => "Paragraf pertama.\nParagraf kedua.", 'stock' => 7]);

        $response = $this->actingAs($this->petugas)->get("/books/{$book->id}");

        $response->assertOk()
            ->assertSee($book->title)
            ->assertSee($book->category->name)
            ->assertSee($book->publisher->name)
            ->assertSee('7 eksemplar')
            ->assertSee('Paragraf kedua.')
            ->assertSee('Sampul belum tersedia')
            ->assertSee(route('books.edit', $book));

        foreach ($book->authors as $author) {
            $response->assertSee($author->name);
        }
    }

    public function test_missing_book_returns_404(): void
    {
        $this->actingAs($this->petugas)->get('/books/999')->assertNotFound();
        $this->actingAs($this->petugas)->get('/books/999/edit')->assertNotFound();
        $this->actingAs($this->petugas)->put('/books/999', $this->validPayload())->assertNotFound();
        $this->actingAs($this->petugas)->delete('/books/999')->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Tambah buku
    |--------------------------------------------------------------------------
    */

    public function test_create_form_lists_reference_data(): void
    {
        $category = Category::factory()->create(['name' => 'Sejarah']);
        $publisher = Publisher::factory()->create(['name' => 'Balai Pustaka']);
        $author = Author::factory()->create(['name' => 'Pramoedya Ananta Toer']);

        $this->actingAs($this->petugas)->get('/books/create')
            ->assertOk()
            ->assertSee('Tambah Buku')
            ->assertSee('Sejarah')
            ->assertSee('Balai Pustaka')
            ->assertSee('Pramoedya Ananta Toer')
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertDontSee('belum tersedia, sehingga buku belum dapat disimpan');
    }

    public function test_create_form_warns_when_reference_data_is_missing(): void
    {
        $this->actingAs($this->petugas)->get('/books/create')
            ->assertOk()
            ->assertSee('Data kategori, penerbit dan penulis belum tersedia');
    }

    public function test_book_can_be_created_with_authors_category_and_publisher(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->petugas)->post('/books', $payload);

        $book = Book::firstOrFail();
        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas('success', 'Buku "Laskar Pelangi" berhasil ditambahkan.');

        $this->assertSame('Laskar Pelangi', $book->title);
        $this->assertSame($payload['category_id'], $book->category_id);
        $this->assertSame($payload['publisher_id'], $book->publisher_id);
        $this->assertEqualsCanonicalizing($payload['authors'], $book->authors->pluck('id')->all());
        $this->assertDatabaseCount('book_authors', 2);
        $this->assertNull($book->cover_image);
    }

    public function test_isbn_is_normalized_before_saving(): void
    {
        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['isbn' => ' 979 8x-12 ']));

        $this->assertSame('9798X-12', Book::firstOrFail()->isbn);

        $this->actingAs($this->petugas)->post('/books', $this->validPayload(['isbn' => '   ']));

        $this->assertNull(Book::latest('id')->firstOrFail()->isbn);
    }

    public function test_invalid_book_data_is_rejected_with_indonesian_messages(): void
    {
        $response = $this->actingAs($this->petugas)->from('/books/create')->post('/books', [
            'title' => '',
            'isbn' => 'ISBN-SALAH!',
            'category_id' => 999,
            'publisher_id' => 'abc',
            'authors' => [999],
            'publication_year' => now()->year + 5,
            'stock' => -1,
            'status' => 'hilang',
        ]);

        $response->assertRedirect('/books/create')->assertSessionHasErrors([
            'title' => 'Judul wajib diisi.',
            'isbn' => 'Format ISBN tidak valid.',
            'category_id' => 'Kategori yang dipilih tidak valid.',
            'publisher_id' => 'Penerbit harus berupa bilangan bulat.',
            'authors.0' => 'Penulis yang dipilih tidak valid.',
            'publication_year' => 'Tahun terbit tidak boleh lebih dari '.(now()->year + 1).'.',
            'stock' => 'Stok harus bernilai minimal 0.',
            'status' => 'Status yang dipilih tidak valid.',
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_authors_are_required(): void
    {
        $this->actingAs($this->petugas)
            ->post('/books', $this->validPayload(['authors' => []]))
            ->assertSessionHasErrors(['authors' => 'Penulis wajib diisi.']);
    }

    public function test_failed_validation_keeps_previous_input(): void
    {
        $this->actingAs($this->petugas)
            ->followingRedirects()
            ->from('/books/create')
            ->post('/books', $this->validPayload(['title' => 'Judul Yang Diketik', 'stock' => 'banyak']))
            ->assertSee('value="Judul Yang Diketik"', false)
            ->assertSee('Stok harus berupa bilangan bulat.');
    }

    public function test_isbn_must_be_unique(): void
    {
        Book::factory()->create(['isbn' => '9786020000001']);

        $this->actingAs($this->petugas)
            ->post('/books', $this->validPayload(['isbn' => '9786020000001']))
            ->assertSessionHasErrors(['isbn' => 'ISBN sudah digunakan.']);

        $this->assertDatabaseCount('books', 1);
    }

    public function test_cover_can_be_uploaded_when_creating(): void
    {
        $this->actingAs($this->petugas)->post('/books', $this->validPayload([
            'cover_image' => UploadedFile::fake()->image('sampul.jpg', 600, 800),
        ]));

        $book = Book::firstOrFail();

        $this->assertStringStartsWith(Book::COVER_DIRECTORY.'/', $book->cover_image);
        Storage::disk(Book::COVER_DISK)->assertExists($book->cover_image);
    }

    public function test_cover_must_be_a_small_image_and_paths_cannot_be_injected(): void
    {
        $this->actingAs($this->petugas)
            ->post('/books', $this->validPayload(['cover_image' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors('cover_image');

        $this->actingAs($this->petugas)
            ->post('/books', $this->validPayload(['cover_image' => UploadedFile::fake()->image('besar.jpg')->size(3000)]))
            ->assertSessionHasErrors(['cover_image' => 'Sampul tidak boleh lebih dari 2048 kilobita.']);

        $this->actingAs($this->petugas)
            ->post('/books', $this->validPayload(['cover_image' => '../../.env']))
            ->assertSessionHasErrors('cover_image');

        $this->assertDatabaseCount('books', 0);
        $this->assertSame([], Storage::disk(Book::COVER_DISK)->allFiles());
    }

    public function test_uploaded_cover_is_removed_when_saving_fails(): void
    {
        Book::creating(fn () => throw new RuntimeException('Simulasi kegagalan database'));

        $this->actingAs($this->petugas)
            ->from('/books/create')
            ->post('/books', $this->validPayload(['cover_image' => UploadedFile::fake()->image('sampul.png')]))
            ->assertRedirect('/books/create')
            ->assertSessionHas('error', 'Buku gagal disimpan. Silakan coba lagi.');

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('book_authors', 0);
        $this->assertSame([], Storage::disk(Book::COVER_DISK)->allFiles());
    }

    /*
    |--------------------------------------------------------------------------
    | Edit buku
    |--------------------------------------------------------------------------
    */

    public function test_edit_form_is_prefilled_with_book_data_and_selected_authors(): void
    {
        $selected = Author::factory()->create(['name' => 'Penulis Terpilih']);
        $other = Author::factory()->create(['name' => 'Penulis Lain']);
        $book = Book::factory()->hasAttached($selected)->create(['title' => 'Judul Lama', 'isbn' => '9780000000011']);

        $response = $this->actingAs($this->petugas)->get("/books/{$book->id}/edit")
            ->assertOk()
            ->assertSee('value="Judul Lama"', false)
            ->assertSee('value="9780000000011"', false);

        $checkbox = fn (Author $author) => '/name="authors\[\]" value="'.$author->id.'"[^>]*>/';
        preg_match($checkbox($selected), $response->getContent(), $selectedTag);
        preg_match($checkbox($other), $response->getContent(), $otherTag);

        $this->assertMatchesRegularExpression('/\schecked\b/', $selectedTag[0]);
        $this->assertDoesNotMatchRegularExpression('/\schecked\b/', $otherTag[0]);
    }

    public function test_book_can_be_updated_and_authors_are_synced(): void
    {
        [$keep, $remove, $add] = Author::factory()->count(3)->create();
        $book = Book::factory()->hasAttached([$keep, $remove])->create();
        $newCategory = Category::factory()->create();

        $response = $this->actingAs($this->petugas)->put("/books/{$book->id}", $this->validPayload([
            'title' => 'Judul Baru',
            'category_id' => $newCategory->id,
            'publisher_id' => $book->publisher_id,
            'authors' => [$keep->id, $add->id],
            'stock' => 3,
            'status' => 'unavailable',
        ]));

        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas('success', 'Buku "Judul Baru" berhasil diperbarui.');

        $book->refresh();
        $this->assertSame('Judul Baru', $book->title);
        $this->assertSame($newCategory->id, $book->category_id);
        $this->assertSame(3, $book->stock);
        $this->assertSame('unavailable', $book->status);
        $this->assertEqualsCanonicalizing([$keep->id, $add->id], $book->authors->pluck('id')->all());
        $this->assertModelExists($remove);
    }

    public function test_isbn_uniqueness_ignores_the_book_being_edited(): void
    {
        $book = Book::factory()->create(['isbn' => '9781111111111']);
        Book::factory()->create(['isbn' => '9782222222222']);

        $this->actingAs($this->petugas)
            ->put("/books/{$book->id}", $this->validPayload(['isbn' => '9781111111111']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->petugas)
            ->put("/books/{$book->id}", $this->validPayload(['isbn' => '9782222222222']))
            ->assertSessionHasErrors(['isbn' => 'ISBN sudah digunakan.']);
    }

    public function test_existing_cover_is_kept_when_no_new_file_is_uploaded(): void
    {
        $path = UploadedFile::fake()->image('lama.jpg')->store(Book::COVER_DIRECTORY, Book::COVER_DISK);
        $book = Book::factory()->create(['cover_image' => $path]);

        $this->actingAs($this->petugas)->put("/books/{$book->id}", $this->validPayload());

        $this->assertSame($path, $book->fresh()->cover_image);
        Storage::disk(Book::COVER_DISK)->assertExists($path);
    }

    public function test_cover_can_be_replaced_and_old_file_is_deleted(): void
    {
        $oldPath = UploadedFile::fake()->image('lama.jpg')->store(Book::COVER_DIRECTORY, Book::COVER_DISK);
        $book = Book::factory()->create(['cover_image' => $oldPath]);

        $this->actingAs($this->petugas)->put("/books/{$book->id}", $this->validPayload([
            'cover_image' => UploadedFile::fake()->image('baru.webp'),
        ]));

        $newPath = $book->fresh()->cover_image;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk(Book::COVER_DISK)->assertExists($newPath);
        Storage::disk(Book::COVER_DISK)->assertMissing($oldPath);
    }

    public function test_cover_can_be_removed(): void
    {
        $path = UploadedFile::fake()->image('lama.jpg')->store(Book::COVER_DIRECTORY, Book::COVER_DISK);
        $book = Book::factory()->create(['cover_image' => $path]);

        $this->actingAs($this->petugas)->put("/books/{$book->id}", $this->validPayload(['remove_cover' => '1']));

        $this->assertNull($book->fresh()->cover_image);
        Storage::disk(Book::COVER_DISK)->assertMissing($path);
    }

    public function test_old_cover_is_kept_when_update_fails(): void
    {
        $oldPath = UploadedFile::fake()->image('lama.jpg')->store(Book::COVER_DIRECTORY, Book::COVER_DISK);
        $book = Book::factory()->create(['cover_image' => $oldPath, 'title' => 'Judul Asli']);
        Book::updating(fn () => throw new RuntimeException('Simulasi kegagalan database'));

        $this->actingAs($this->petugas)
            ->put("/books/{$book->id}", $this->validPayload(['title' => 'Gagal', 'cover_image' => UploadedFile::fake()->image('baru.jpg')]))
            ->assertSessionHas('error', 'Perubahan buku gagal disimpan. Silakan coba lagi.');

        $this->assertSame('Judul Asli', $book->fresh()->title);
        $this->assertSame([$oldPath], Storage::disk(Book::COVER_DISK)->allFiles());
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus buku
    |--------------------------------------------------------------------------
    */

    public function test_book_can_be_deleted_without_deleting_its_references(): void
    {
        $path = UploadedFile::fake()->image('sampul.jpg')->store(Book::COVER_DIRECTORY, Book::COVER_DISK);
        $authors = Author::factory()->count(2)->create();
        $book = Book::factory()->hasAttached($authors)->create(['title' => 'Akan Dihapus', 'cover_image' => $path]);
        $otherBook = Book::factory()->hasAttached($authors)->create();

        $this->actingAs($this->petugas)->delete("/books/{$book->id}")
            ->assertRedirect(route('books.index'))
            ->assertSessionHas('success', 'Buku "Akan Dihapus" berhasil dihapus.');

        $this->assertModelMissing($book);
        $this->assertDatabaseMissing('book_authors', ['book_id' => $book->id]);
        $this->assertDatabaseCount('book_authors', 2);
        $this->assertModelExists($book->category);
        $this->assertModelExists($book->publisher);
        $authors->each(fn (Author $author) => $this->assertModelExists($author));
        $this->assertModelExists($otherBook);
        Storage::disk(Book::COVER_DISK)->assertMissing($path);
    }

    public function test_admin_can_also_delete_books(): void
    {
        $book = Book::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->delete("/books/{$book->id}");

        $this->assertModelMissing($book);
    }

    public function test_catalog_and_detail_pages_have_working_delete_confirmation(): void
    {
        $book = Book::factory()->create();

        foreach (['/books', "/books/{$book->id}"] as $uri) {
            $this->actingAs($this->petugas)->get($uri)
                ->assertSee('Hapus buku?')
                ->assertSee('name="_method" value="DELETE"', false)
                ->assertSee("\$dispatch('confirm-delete', { action: 'http:\\/\\/", false)
                // Direktif Blade yang tidak terkompilasi akan merusak JavaScript tombol hapus.
                ->assertDontSee('@js(', false);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();
        $authors = Author::factory()->count(2)->create();

        return array_merge([
            'title' => 'Laskar Pelangi',
            'isbn' => null,
            'description' => 'Kisah sepuluh anak di Belitung.',
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'authors' => $authors->pluck('id')->all(),
            'publication_year' => 2005,
            'stock' => 5,
            'status' => 'available',
        ], $overrides);
    }
}
