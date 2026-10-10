<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$name, $type] = fake()->randomElement([
            ['Wi-Fi', 'jaringan'],
            ['AC', 'kenyamanan'],
            ['Stopkontak', 'kelistrikan'],
            ['Komputer', 'elektronik'],
            ['Proyektor', 'elektronik'],
            ['Loker', 'penyimpanan'],
        ]);

        return [
            'name' => $name,
            'type' => $type,
            'description' => fake()->optional()->sentence(),
            'status' => 'available',
        ];
    }

    /**
     * Indicate that the facility is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unavailable',
        ]);
    }
}
