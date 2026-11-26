<?php

namespace Database\Factories;

use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\GraduationProcess;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectVersion>
 */
class ProjectVersionFactory extends Factory
{
    /**
     * Define the model's default state: a project profile in review without reviewer.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'graduation_process_id' => GraduationProcess::factory()->inPhase(Phase::ProfileEvaluation),
            'number' => 1,
            'phase' => Phase::ProfileEvaluation,
            'title' => fake()->sentence(8),
            'file_path' => 'tramites/versiones/'.Str::uuid().'.pdf',
            'original_file_name' => fake()->word().'.pdf',
            'file_size_bytes' => fake()->numberBetween(10_000, 5_000_000),
            'status' => ProjectVersionStatus::InReview,
            'reviewer_id' => null,
            'submitted_at' => now()->subDays(3),
            'evaluated_at' => null,
        ];
    }

    /**
     * A project profile in review with the given reviewer, or without one.
     */
    public function profileInReview(?Teacher $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => Phase::ProfileEvaluation,
            'status' => ProjectVersionStatus::InReview,
            'reviewer_id' => $reviewer?->id,
            'evaluated_at' => null,
        ]);
    }

    /**
     * A project profile the reviewer approved.
     */
    public function profileApproved(?Teacher $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => Phase::ProfileEvaluation,
            'status' => ProjectVersionStatus::Approved,
            'reviewer_id' => $reviewer->id ?? $this->workshopTeacherId(),
            'evaluated_at' => now()->subDay(),
        ]);
    }

    /**
     * A project profile the reviewer rejected.
     */
    public function profileRejected(?Teacher $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => Phase::ProfileEvaluation,
            'status' => ProjectVersionStatus::Rejected,
            'reviewer_id' => $reviewer->id ?? $this->workshopTeacherId(),
            'evaluated_at' => now()->subDay(),
        ]);
    }

    /**
     * A pre-defense version in review, waiting for the committee pronouncements (UC-008).
     */
    public function preDefenseInReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => Phase::PreDefense,
            'status' => ProjectVersionStatus::InReview,
            'reviewer_id' => null,
            'evaluated_at' => null,
        ]);
    }

    /**
     * A pre-defense version the committee approved.
     */
    public function preDefenseApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => Phase::PreDefense,
            'status' => ProjectVersionStatus::Approved,
            'reviewer_id' => null,
            'evaluated_at' => now()->subDay(),
        ]);
    }

    /**
     * A pre-defense version the committee left with observations.
     */
    public function preDefenseWithObservations(): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => Phase::PreDefense,
            'status' => ProjectVersionStatus::WithObservations,
            'reviewer_id' => null,
            'evaluated_at' => now()->subDay(),
        ]);
    }

    /**
     * The designated workshop teacher, created when there is none (UC-003 BR-001: at most one).
     */
    private function workshopTeacherId(): int
    {
        return Teacher::query()->where('is_workshop_teacher', true)->value('id')
            ?? Teacher::factory()->workshopTeacher()->create()->id;
    }
}
