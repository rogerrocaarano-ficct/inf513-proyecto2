<?php

namespace Database\Factories;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Models\Defense;
use App\Models\GraduationProcess;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Defense>
 */
class DefenseFactory extends Factory
{
    /**
     * Define the model's default state: a pre-defense scheduled and not held.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'graduation_process_id' => GraduationProcess::factory()->inPhase(Phase::PreDefense),
            'type' => DefenseType::PreDefense,
            'scheduled_at' => now()->addWeek()->setTime(10, 0),
            'location' => null,
            'schedule_revision' => 1,
            'grade' => null,
            'is_held' => false,
            'is_student_absent' => false,
            'absence_recorded_by_id' => null,
        ];
    }

    /**
     * A formal defense scheduled and not held.
     */
    public function formalDefense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DefenseType::FormalDefense,
        ]);
    }

    /**
     * A pre-defense or formal defense already held.
     */
    public function held(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_held' => true,
        ]);
    }
}
