<?php

namespace Database\Factories;

use App\Models\MembershipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipType>
 */
class MembershipTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Reguler', 'Pelajar', 'Mahasiswa', 'Umum']),
            'description' => fake()->optional()->sentence(),
            'duration_months' => fake()->randomElement([3, 6, 12]),
            'max_borrowed_books' => fake()->numberBetween(1, 5),
        ];
    }
}
