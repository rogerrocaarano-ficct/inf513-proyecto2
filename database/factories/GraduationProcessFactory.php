<?php

namespace Database\Factories;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\PhaseChange;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GraduationProcess>
 */
class GraduationProcessFactory extends Factory
{
    /**
     * Define the model's default state: a process in the requirements phase, without tutor.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'tutor_id' => null,
            'proposed_tutor_name' => null,
            'project_title' => null,
            'phase' => Phase::Requirements,
            'started_at' => now()->subMonths(2),
            'concluded_at' => null,
            'abandoned_at' => null,
            'archived_at' => null,
        ];
    }

    /**
     * Record the entry into the current phase, as every process has at least one phase change.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (GraduationProcess $process): void {
            PhaseChange::query()->create([
                'graduation_process_id' => $process->id,
                'phase' => $process->phase,
                'entered_at' => $process->started_at,
                'changed_by' => $process->student->user_id,
            ]);
        });
    }

    /**
     * A process with the given tutor, or a new one, in the profile evaluation phase.
     */
    public function withTutor(?Teacher $tutor = null): static
    {
        return $this->state(fn (array $attributes) => [
            'tutor_id' => $tutor->id ?? Teacher::factory(),
            'phase' => $attributes['phase'] === Phase::Requirements ? Phase::ProfileEvaluation : $attributes['phase'],
        ]);
    }

    /**
     * A process in the given phase; from profile evaluation on, it has a tutor.
     */
    public function inPhase(Phase $phase): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => $phase,
            'tutor_id' => $phase === Phase::Requirements ? $attributes['tutor_id'] : ($attributes['tutor_id'] ?? Teacher::factory()),
            'archived_at' => $phase === Phase::Archive ? now() : null,
        ]);
    }

    /**
     * A process in the pre-defense phase with three current committee members.
     */
    public function withCommittee(): static
    {
        return $this->inPhase(Phase::PreDefense)
            ->state(fn (array $attributes) => ['project_title' => fake()->sentence(8)])
            ->afterCreating(function (GraduationProcess $process): void {
                CommitteeMember::factory()->count(3)->for($process)->create();
            });
    }

    /**
     * A process concluded with a held formal defense graded with the given grade.
     */
    public function concluded(int $grade = 70): static
    {
        return $this->inPhase(Phase::FormalDefense)
            ->state(fn (array $attributes) => [
                'project_title' => fake()->sentence(8),
                'concluded_at' => now()->subDay(),
            ])
            ->afterCreating(function (GraduationProcess $process) use ($grade): void {
                Defense::factory()->for($process)->held()->create([
                    'type' => DefenseType::FormalDefense,
                    'scheduled_at' => now()->subDays(2),
                    'grade' => $grade,
                ]);
            });
    }

    /**
     * A process that concluded approved (UC-001 BR-009).
     */
    public function approved(): static
    {
        return $this->concluded(GraduationProcess::PASSING_GRADE + 19);
    }

    /**
     * A process that concluded failed.
     */
    public function failed(): static
    {
        return $this->concluded(GraduationProcess::PASSING_GRADE - 11);
    }

    /**
     * A process abandoned because the student did not attend the pre-defense (UC-001 BR-008).
     */
    public function abandoned(): static
    {
        return $this->inPhase(Phase::PreDefense)
            ->state(fn (array $attributes) => ['abandoned_at' => now()->subDay()])
            ->afterCreating(function (GraduationProcess $process): void {
                $member = CommitteeMember::factory()->for($process)->create();
                CommitteeMember::factory()->count(2)->for($process)->create();

                Defense::factory()->for($process)->held()->create([
                    'scheduled_at' => now()->subDays(2),
                    'is_student_absent' => true,
                    'absence_recorded_by_id' => $member->id,
                ]);
            });
    }
}
