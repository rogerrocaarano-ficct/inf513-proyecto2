<?php

namespace App\Actions\TutorObservations;

use App\Models\GraduationProcess;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Lists the processes in progress the user tutors, those with a version the tutor did not observe first (UC-007).
 */
class ListTutoredProcesses
{
    /**
     * @return Collection<int, TutoredProcess>
     */
    public function handle(User $tutor): Collection
    {
        $teacherId = $tutor->teacher?->id;

        if ($teacherId === null) {
            return new Collection;
        }

        // UC-007 BR-001: A tutor sees only the processes in progress they currently tutor.
        return GraduationProcess::query()
            ->inProgress()
            ->where('tutor_id', $teacherId)
            ->with(['student.user', 'projectVersions.observations'])
            ->get()
            ->map(function (GraduationProcess $process) use ($tutor): TutoredProcess {
                $versions = $process->projectVersions;

                return new TutoredProcess(
                    $process,
                    $versions->sortByDesc('number')->first(),
                    $versions->contains(fn (ProjectVersion $version): bool => $version->observations
                        ->doesntContain('author_id', $tutor->id)),
                );
            })
            ->sortBy([
                fn (TutoredProcess $a, TutoredProcess $b): int => (int) $b->hasUnobservedVersion <=> (int) $a->hasUnobservedVersion,
                fn (TutoredProcess $a, TutoredProcess $b): int => $a->process->started_at <=> $b->process->started_at,
            ])
            ->values();
    }
}
