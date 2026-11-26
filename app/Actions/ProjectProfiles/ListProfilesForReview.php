<?php

namespace App\Actions\ProjectProfiles;

use App\Enums\ProjectVersionStatus;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Lists the project profiles in review that the user, as reviewer, has to evaluate.
 */
class ListProfilesForReview
{
    /**
     * @return Collection<int, ProjectVersion> The oldest submission first
     */
    public function handle(User $reviewer): Collection
    {
        $teacherId = $reviewer->teacher?->id;

        if ($teacherId === null) {
            return new Collection;
        }

        // UC-005 BR-001: A reviewer sees only the project profiles in review assigned to them.
        return ProjectVersion::query()
            ->profiles()
            ->where('status', ProjectVersionStatus::InReview)
            ->where('reviewer_id', $teacherId)
            ->with('graduationProcess.student.user')
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->get();
    }
}
