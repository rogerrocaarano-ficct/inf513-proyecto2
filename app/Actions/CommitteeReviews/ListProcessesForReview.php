<?php

namespace App\Actions\CommitteeReviews;

use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Lists the in-progress processes whose committee the user currently joins, pending reviews first (UC-008).
 */
class ListProcessesForReview
{
    /**
     * @return Collection<int, ReviewProcess>
     */
    public function handle(User $reviewer): Collection
    {
        $teacherId = $reviewer->teacher?->id;

        if ($teacherId === null) {
            return new Collection;
        }

        return GraduationProcess::query()
            ->inProgress()
            // UC-008 BR-001: A member sees only the processes whose current committee they join.
            ->whereHas('currentCommitteeMembers', fn (Builder $query) => $query
                ->where('teacher_id', $teacherId))
            ->with(['student.user', 'currentCommitteeMembers', 'projectVersions.observations', 'projectVersions.versionApprovals'])
            ->get()
            ->map(function (GraduationProcess $process) use ($reviewer, $teacherId): ReviewProcess {
                $member = $process->currentCommitteeMembers->first(
                    fn (CommitteeMember $committeeMember): bool => $committeeMember->teacher_id === $teacherId,
                );
                $versions = $process->projectVersions->sortByDesc('number');

                // UC-008 BR-002: A version no longer waits for a member who already pronounced.
                $pendingVersion = $member === null ? null : $versions->first(
                    fn (ProjectVersion $version): bool => $version->phase === Phase::PreDefense
                        && $version->status === ProjectVersionStatus::InReview
                        && ! $version->versionApprovals->contains('committee_member_id', $member->id)
                        && ! $version->observations->contains('author_id', $reviewer->id),
                );

                return new ReviewProcess($process, $pendingVersion, $versions->first());
            })
            ->sort(function (ReviewProcess $a, ReviewProcess $b): int {
                if ($a->hasPendingVersion() !== $b->hasPendingVersion()) {
                    return $a->hasPendingVersion() ? -1 : 1;
                }

                if ($a->process->started_at->equalTo($b->process->started_at)) {
                    return $a->process->id <=> $b->process->id;
                }

                return $a->process->started_at->lessThan($b->process->started_at) ? -1 : 1;
            })
            ->values();
    }
}
