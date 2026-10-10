<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MembershipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // membership_number dan card_number bersifat unique.
            'membership_number' => fake()->unique()->numerify('MBR-######'),
            'name' => fake()->name(),
            'identity_number' => fake()->numerify('################'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'join_date' => fake()->dateTimeBetween('-2 years')->format('Y-m-d'),
            'membership_type_id' => MembershipType::factory(),
            'card_number' => fake()->unique()->numerify('CARD-########'),
            'photo' => null,
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the member is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
