<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Member;
use App\Models\MembershipType;
use App\Models\Publisher;
use App\Models\ReadingArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_belongs_to_category_and_publisher(): void
    {
        $book = $this->createBook('Laskar Pelangi');

        $this->assertTrue($book->category->is(Category::first()));
        $this->assertTrue($book->publisher->is(Publisher::first()));
        $this->assertTrue($book->category->books->first()->is($book));
        $this->assertTrue($book->publisher->books->first()->is($book));
    }

    public function test_books_and_authors_are_many_to_many_in_both_directions(): void
    {
        $first = $this->createBook('Laskar Pelangi');
        $second = $this->createBook('Sang Pemimpi');

        $andrea = Author::create(['name' => 'Andrea Hirata']);
        $coAuthor = Author::create(['name' => 'Penulis Pendamping']);

        $first->authors()->attach([$andrea->id, $coAuthor->id]);
        $andrea->books()->attach($second->id);

        $this->assertDatabaseHas('book_authors', ['book_id' => $first->id, 'author_id' => $andrea->id]);
        $this->assertDatabaseHas('book_authors', ['book_id' => $first->id, 'author_id' => $coAuthor->id]);
        $this->assertDatabaseHas('book_authors', ['book_id' => $second->id, 'author_id' => $andrea->id]);

        $this->assertEqualsCanonicalizing(
            ['Andrea Hirata', 'Penulis Pendamping'],
            $first->authors->pluck('name')->all(),
        );
        $this->assertEqualsCanonicalizing(
            ['Laskar Pelangi', 'Sang Pemimpi'],
            $andrea->books->pluck('title')->all(),
        );
    }

    public function test_book_authors_can_be_synced_and_eager_loaded(): void
    {
        $book = $this->createBook('Bumi Manusia');
        $authors = collect(['Pramoedya', 'Penulis A', 'Penulis B'])
            ->map(fn (string $name) => Author::create(['name' => $name]));

        $book->authors()->sync($authors->pluck('id'));
        $book->authors()->sync($authors->take(1)->pluck('id'));

        $loaded = Book::with(['authors', 'category', 'publisher'])->find($book->id);

        $this->assertTrue($loaded->relationLoaded('authors'));
        $this->assertSame(['Pramoedya'], $loaded->authors->pluck('name')->all());
        $this->assertDatabaseCount('book_authors', 1);
    }

    public function test_deleting_a_book_removes_its_pivot_rows_but_keeps_authors(): void
    {
        $book = $this->createBook('Ronggeng Dukuh Paruk');
        $author = Author::create(['name' => 'Ahmad Tohari']);
        $book->authors()->attach($author->id);

        $book->delete();

        $this->assertDatabaseMissing('book_authors', ['author_id' => $author->id]);
        $this->assertModelExists($author);
    }

    public function test_reading_areas_and_facilities_are_many_to_many_in_both_directions(): void
    {
        $lantai1 = ReadingArea::create(['name' => 'Ruang Baca Lantai 1', 'floor_number' => 1, 'capacity' => 30]);
        $lantai2 = ReadingArea::create(['name' => 'Ruang Baca Lantai 2', 'floor_number' => 2, 'capacity' => 20]);

        $wifi = Facility::create(['name' => 'Wi-Fi', 'type' => 'jaringan']);
        $ac = Facility::create(['name' => 'AC', 'type' => 'kenyamanan']);

        $lantai1->facilities()->attach([$wifi->id, $ac->id]);
        $wifi->readingAreas()->attach($lantai2->id);

        $this->assertDatabaseHas('area_facilities', ['area_id' => $lantai1->id, 'facility_id' => $wifi->id]);
        $this->assertDatabaseHas('area_facilities', ['area_id' => $lantai1->id, 'facility_id' => $ac->id]);
        $this->assertDatabaseHas('area_facilities', ['area_id' => $lantai2->id, 'facility_id' => $wifi->id]);

        $this->assertEqualsCanonicalizing(['Wi-Fi', 'AC'], $lantai1->facilities->pluck('name')->all());
        $this->assertEqualsCanonicalizing(
            ['Ruang Baca Lantai 1', 'Ruang Baca Lantai 2'],
            $wifi->readingAreas->pluck('name')->all(),
        );

        $lantai1->facilities()->sync([$ac->id]);

        $this->assertSame(['AC'], $lantai1->fresh()->facilities->pluck('name')->all());
        $this->assertSame(['Ruang Baca Lantai 2'], $wifi->fresh()->readingAreas->pluck('name')->all());
    }

    public function test_member_belongs_to_membership_type(): void
    {
        $type = MembershipType::create([
            'name' => 'Reguler',
            'description' => 'Keanggotaan umum',
            'duration_months' => 12,
            'max_borrowed_books' => 3,
        ]);

        $member = Member::create([
            'membership_number' => 'M-001',
            'name' => 'Budi Santoso',
            'join_date' => '2026-10-01',
            'membership_type_id' => $type->id,
        ]);

        $this->assertTrue($member->membershipType->is($type));
        $this->assertTrue($type->members->first()->is($member));
        $this->assertSame(1, $type->members()->count());
    }

    public function test_membership_type_only_mass_assigns_its_own_columns(): void
    {
        $type = MembershipType::create([
            'id' => 999,
            'name' => 'Pelajar',
            'duration_months' => 6,
            'max_borrowed_books' => 2,
        ]);

        $this->assertNotSame(999, $type->id);
        $this->assertSame(
            ['name', 'description', 'duration_months', 'max_borrowed_books'],
            $type->getFillable(),
        );
    }

    private function createBook(string $title): Book
    {
        $category = Category::firstOrCreate(['name' => 'Fiksi']);
        $publisher = Publisher::firstOrCreate(['name' => 'Bentang Pustaka', 'type' => 'publisher']);

        return Book::create([
            'title' => $title,
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2005,
        ]);
    }
}
