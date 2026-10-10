<?php

namespace Database\Factories;

use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publisher>
 */
class PublisherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement(['publisher', 'distributor']),
            'contact_person' => fake()->name(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'region' => fake()->city(),
            'notes' => null,
        ];
    }

    /**
     * Indicate that the record is a publisher (penerbit).
     */
    public function publisher(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'publisher',
        ]);
    }

    /**
     * Indicate that the record is a distributor.
     */
    public function distributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'distributor',
        ]);
    }
}
