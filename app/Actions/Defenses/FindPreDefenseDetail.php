<?php

namespace App\Actions\Defenses;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\Defense;
use Illuminate\Validation\ValidationException;

/**
 * Finds the approved pre-defense version to download and the members who already registered the act (UC-010).
 */
class FindPreDefenseDetail
{
    public function handle(Defense $defense, CommitteeMember $member): PreDefenseDetail
    {
        if ($defense->type !== DefenseType::PreDefense) {
            throw ValidationException::withMessages([
                'defense' => 'El acto seleccionado no es una pre-defensa.',
            ]);
        }

        // UC-010 NFR-004: Only a current member of this tribunal opens the pre-defense detail.
        if ($member->graduation_process_id !== $defense->graduation_process_id || $member->replaced_at !== null) {
            throw ValidationException::withMessages([
                'defense' => 'No es miembro vigente del tribunal de esta pre-defensa.',
            ]);
        }

        $process = $defense->loadMissing('graduationProcess')->graduationProcess;

        $version = $process->projectVersions()
            ->where('phase', Phase::PreDefense)
            ->where('status', ProjectVersionStatus::Approved)
            ->orderByDesc('number')
            ->first();

        // UC-010 BR-001: The detail shows who registered, but never their observations to other members.
        $reportedBy = $defense->committeeMembers()
            ->with('teacher.user')
            ->orderBy('committee_members.id')
            ->get();

        return new PreDefenseDetail($defense, $version, $reportedBy);
    }
}
