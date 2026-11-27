<?php

namespace App\Actions\Defenses;

use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\User;

/**
 * Resolves the teacher's current membership in the tribunal of a graduation process (UC-010, NFR-004).
 */
final readonly class FindCurrentCommitteeMember
{
    public function forDefense(User $user, Defense $defense): ?CommitteeMember
    {
        return $this->forProcess($user, $defense->loadMissing('graduationProcess')->graduationProcess);
    }

    public function forProcess(User $user, GraduationProcess $process): ?CommitteeMember
    {
        $teacherId = $user->teacher?->id;

        if ($teacherId === null) {
            return null;
        }

        // UC-010 BR-001: a replaced member no longer participates in the process (NFR-004).
        return $process->currentCommitteeMembers()
            ->where('teacher_id', $teacherId)
            ->first();
    }
}
