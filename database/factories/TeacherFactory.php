<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->withRoles(RoleName::Teacher),
            'is_workshop_teacher' => false,
            'is_active' => true,
        ];
    }

    /**
     * The designated workshop teacher (UC-003).
     */
    public function workshopTeacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_workshop_teacher' => true,
            'is_active' => true,
        ]);
    }

    /**
     * A teacher who no longer takes part in new graduation processes, with a deactivated account.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->inactive()->withRoles(RoleName::Teacher),
            'is_active' => false,
        ]);
    }

    /**
     * The career director, who is also a teacher (UC-018 BR-002).
     */
    public function careerDirector(): static
    {
        return $this->afterCreating(function (Teacher $teacher): void {
            $teacher->user->roles()->syncWithoutDetaching(
                Role::query()->where('name', RoleName::CareerDirector)->pluck('id'),
            );
        });
    }
}
