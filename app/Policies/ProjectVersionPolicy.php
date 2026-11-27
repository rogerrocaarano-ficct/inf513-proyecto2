<?php

namespace App\Policies;

use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Who evaluates, observes, reviews and downloads a project version (UC-005, UC-007, UC-008).
 */
class ProjectVersionPolicy
{
    public const string NOT_CURRENT_MEMBER = 'Solo un miembro vigente del tribunal evaluador puede revisar la versión del proyecto.';

    /**
     * UC-005 BR-001: Only the reviewer a project profile is assigned to can evaluate it.
     */
    public function evaluate(User $user, ProjectVersion $profile): Response
    {
        return $this->isReviewer($user, $profile)
            ? Response::allow()
            : Response::deny('Solo el revisor del perfil de proyecto puede evaluarlo.');
    }

    /**
     * UC-007 BR-001, BR-002: The current tutor of a process in progress observes any of its project versions.
     */
    public function observe(User $user, ProjectVersion $version): Response
    {
        return Gate::forUser($user)->inspect('viewAsTutor', $version->graduationProcess);
    }

    /**
     * UC-008 BR-001: Only a current member of the committee of a pre-defense version reviews it.
     */
    public function review(User $user, ProjectVersion $version): Response
    {
        $teacherId = $user->teacher?->id;
        $isCurrentMember = $teacherId !== null
            && $version->phase === Phase::PreDefense
            && GraduationProcess::query()->whereKey($version->graduation_process_id)->inProgress()->exists()
            && CommitteeMember::query()
                ->where('graduation_process_id', $version->graduation_process_id)
                ->where('teacher_id', $teacherId)
                ->current()
                ->exists();

        return $isCurrentMember
            ? Response::allow()
            : Response::deny(self::NOT_CURRENT_MEMBER);
    }

    /**
     * UC-005 BR-005: The reviewer also downloads the earlier rejected profiles of the process in review.
     */
    public function download(User $user, ProjectVersion $profile): bool
    {
        if ($this->isReviewer($user, $profile)) {
            return true;
        }

        // UC-007 step 4: The current tutor downloads the versions of a process in progress.
        if ($this->observe($user, $profile)->allowed()) {
            return true;
        }

        // UC-008 step 4: The current committee member downloads the version under review.
        if ($this->review($user, $profile)->allowed()) {
            return true;
        }

        $teacherId = $user->teacher?->id;

        if ($teacherId === null || $profile->status !== ProjectVersionStatus::Rejected) {
            return false;
        }

        return ProjectVersion::query()
            ->profiles()
            ->where('graduation_process_id', $profile->graduation_process_id)
            ->where('status', ProjectVersionStatus::InReview)
            ->where('reviewer_id', $teacherId)
            ->where('number', '>', $profile->number)
            ->exists();
    }

    private function isReviewer(User $user, ProjectVersion $profile): bool
    {
        $teacherId = $user->teacher?->id;

        return $teacherId !== null && $profile->reviewer_id === $teacherId;
    }
}
