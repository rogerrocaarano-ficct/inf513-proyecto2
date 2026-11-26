<?php

namespace App\Actions\Committees;

use App\Enums\ProjectVersionStatus;
use App\Models\GraduationProcess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Lists the graduation processes in progress with an approved project profile, those without committee first.
 */
class ListProcessesForCommittee
{
    /**
     * @return Builder<GraduationProcess>
     */
    public function query(): Builder
    {
        // UC-006 BR-003: Only processes in progress with an approved project profile receive a committee.
        return GraduationProcess::query()
            ->inProgress()
            ->whereHas('projectVersions', fn (Builder $query) => $query
                ->profiles()
                ->where('status', ProjectVersionStatus::Approved));
    }

    /**
     * @return Collection<int, GraduationProcess>
     */
    public function handle(): Collection
    {
        return $this->query()
            ->with(['student.user', 'tutor.user', 'currentCommitteeMembers.teacher.user'])
            ->orderByRaw('exists (select 1 from committee_members where graduation_process_id = graduation_processes.id and replaced_at is null)')
            ->orderBy('started_at')
            ->get();
    }
}
