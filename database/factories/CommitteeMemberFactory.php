<?php

namespace Database\Factories;

use App\Enums\Phase;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommitteeMember>
 */
class CommitteeMemberFactory extends Factory
{
    /**
     * Define the model's default state: a current member.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'graduation_process_id' => GraduationProcess::factory()->inPhase(Phase::PreDefense),
            'teacher_id' => Teacher::factory(),
            'assigned_at' => now()->subMonth(),
            'replaced_at' => null,
        ];
    }

    /**
     * A member the career director replaced.
     */
    public function replaced(): static
    {
        return $this->state(fn (array $attributes) => [
            'replaced_at' => now()->subDay(),
        ]);
    }
}
