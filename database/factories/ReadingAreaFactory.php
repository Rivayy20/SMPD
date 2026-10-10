<?php

namespace Database\Factories;

use App\Models\ReadingArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReadingArea>
 */
class ReadingAreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Fasilitas tidak dibuat otomatis. Gunakan:
     * ReadingArea::factory()->hasAttached(Facility::factory()->count(3))->create();
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Ruang Baca '.fake()->randomElement(['Umum', 'Anak', 'Referensi', 'Diskusi', 'Tenang']),
            'floor_number' => fake()->numberBetween(1, 4),
            'capacity' => fake()->numberBetween(10, 60),
            'description' => fake()->optional()->sentence(),
            'status' => 'available',
        ];
    }

    /**
     * Indicate that the reading area is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unavailable',
        ]);
    }
}
