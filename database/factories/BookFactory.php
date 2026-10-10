<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Penulis tidak dibuat otomatis. Gunakan:
     * Book::factory()->hasAttached(Author::factory()->count(2))->create();
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(3), '.'),
            // Kolom isbn bersifat unique (maks. 20 karakter); ISBN-13 = 13 digit.
            'isbn' => fake()->unique()->isbn13(),
            'description' => fake()->optional()->paragraph(),
            'category_id' => Category::factory(),
            'publisher_id' => Publisher::factory(),
            'publication_year' => fake()->numberBetween(1980, (int) date('Y')),
            'stock' => fake()->numberBetween(0, 20),
            'cover_image' => null,
            'status' => 'available',
        ];
    }

    /**
     * Indicate that the book is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unavailable',
        ]);
    }
}
