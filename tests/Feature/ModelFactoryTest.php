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
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_factory_creates_a_persisted_model(): void
    {
        $models = [
            Category::class,
            Publisher::class,
            Author::class,
            Book::class,
            MembershipType::class,
            Member::class,
            ReadingArea::class,
            Facility::class,
        ];

        foreach ($models as $model) {
            $this->assertModelExists($model::factory()->create());
        }
    }

    public function test_book_factory_creates_valid_foreign_keys(): void
    {
        $book = Book::factory()->create();

        $this->assertModelExists($book->category);
        $this->assertModelExists($book->publisher);
        $this->assertLessThanOrEqual(20, strlen($book->isbn));
        $this->assertContains($book->status, ['available', 'unavailable']);
    }

    public function test_book_factory_can_reuse_existing_category_and_publisher(): void
    {
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->publisher()->create();

        Book::factory()->count(3)->for($category)->for($publisher)->create();

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseCount('publishers', 1);
        $this->assertSame(3, $category->books()->count());
    }

    public function test_factories_respect_unique_constraints_in_bulk(): void
    {
        Category::factory()->count(30)->create();
        Book::factory()->count(30)->create();
        Member::factory()->count(30)->create();

        $this->assertSame(60, Category::distinct()->count('name'));
        $this->assertSame(30, Book::distinct()->count('isbn'));
        $this->assertSame(30, Member::distinct()->count('membership_number'));
        $this->assertSame(30, Member::distinct()->count('card_number'));
    }

    public function test_member_factory_creates_valid_membership_type_and_join_date(): void
    {
        $member = Member::factory()->create();

        $this->assertModelExists($member->membershipType);
        $this->assertInstanceOf(CarbonInterface::class, $member->fresh()->join_date);
        $this->assertLessThanOrEqual(50, strlen($member->membership_number));
        $this->assertLessThanOrEqual(50, strlen($member->card_number));
    }

    public function test_many_to_many_factories_attach_through_the_correct_pivot_tables(): void
    {
        $book = Book::factory()->hasAttached(Author::factory()->count(2))->create();
        $area = ReadingArea::factory()->hasAttached(Facility::factory()->count(3))->create();

        $this->assertCount(2, $book->authors);
        $this->assertDatabaseCount('book_authors', 2);

        $this->assertCount(3, $area->facilities);
        $this->assertDatabaseCount('area_facilities', 3);
    }

    public function test_factory_states_match_enum_values_in_migrations(): void
    {
        $this->assertSame('publisher', Publisher::factory()->publisher()->create()->type);
        $this->assertSame('distributor', Publisher::factory()->distributor()->create()->type);
        $this->assertSame('unavailable', Book::factory()->unavailable()->create()->status);
        $this->assertSame('inactive', Member::factory()->inactive()->create()->status);
        $this->assertSame('unavailable', ReadingArea::factory()->unavailable()->create()->status);
        $this->assertSame('unavailable', Facility::factory()->unavailable()->create()->status);
    }
}
