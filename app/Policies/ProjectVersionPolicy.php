<?php

namespace App\Policies;

use App\Enums\ProjectVersionStatus;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Who evaluates and downloads a project profile (UC-005).
 */
class ProjectVersionPolicy
{
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
     * UC-005 BR-005: The reviewer also downloads the earlier rejected profiles of the process in review.
     */
    public function download(User $user, ProjectVersion $profile): bool
    {
        if ($this->isReviewer($user, $profile)) {
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
