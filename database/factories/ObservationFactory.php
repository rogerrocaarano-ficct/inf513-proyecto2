<?php

namespace Database\Factories;

use App\Models\Defense;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Observation>
 */
class ObservationFactory extends Factory
{
    /**
     * Define the model's default state: an observation on a project version.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'project_version_id' => ProjectVersion::factory(),
            'defense_id' => null,
            'body' => fake()->paragraph(),
            'recorded_at' => now(),
        ];
    }

    /**
     * An observation on the given project version.
     */
    public function forVersion(ProjectVersion $version): static
    {
        return $this->state(fn (array $attributes) => [
            'project_version_id' => $version->id,
            'defense_id' => null,
        ]);
    }

    /**
     * An observation on the given pre-defense.
     */
    public function forDefense(Defense $defense): static
    {
        return $this->state(fn (array $attributes) => [
            'project_version_id' => null,
            'defense_id' => $defense->id,
        ]);
    }
}
