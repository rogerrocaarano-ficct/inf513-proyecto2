<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withRoles(RoleName::Student),
            'registration_number' => fake()->unique()->numerify('2########'),
            'id_card_number' => fake()->unique()->numerify('#######'),
            'id_card_complement' => null,
            'phone' => fake()->numerify('7#######'),
        ];
    }
}
