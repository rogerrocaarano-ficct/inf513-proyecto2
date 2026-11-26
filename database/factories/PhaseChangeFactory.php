<?php

namespace Database\Factories;

use App\Enums\Phase;
use App\Models\GraduationProcess;
use App\Models\PhaseChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhaseChange>
 */
class PhaseChangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'graduation_process_id' => GraduationProcess::factory(),
            'phase' => Phase::ProfileEvaluation,
            'entered_at' => now(),
            'changed_by' => User::factory(),
        ];
    }
}
