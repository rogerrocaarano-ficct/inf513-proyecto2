<?php

namespace Database\Factories;

use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\DefenseReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DefenseReview>
 */
class DefenseReviewFactory extends Factory
{
    /**
     * Define the model's default state: a registration without observations or grade.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'defense_id' => Defense::factory(),
            'committee_member_id' => CommitteeMember::factory(),
            'has_observations' => false,
            'grade' => null,
            'recorded_at' => now(),
        ];
    }
}
