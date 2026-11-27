<?php

namespace App\Actions\ProjectVersions;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\GraduationProcess;
use App\Models\User;

/**
 * Finds the process in progress of a student and whether its current phase admits a new project version (UC-004).
 */
class FindVersionAdmission
{
    public function handle(User $student): VersionAdmission
    {
        $process = GraduationProcess::query()
            ->inProgress()
            ->where('phase', '!=', Phase::Archive)
            ->whereHas('student', fn ($query) => $query->where('user_id', $student->id))
            ->latest('id')
            ->first();

        if ($process === null) {
            return new VersionAdmission(null, 'No tiene un trámite de titulación en curso.', null);
        }

        $lastVersion = $process->projectVersions()->reorder('number', 'desc')->first();

        return new VersionAdmission($process, $this->reasonNotAdmitted($process), $lastVersion);
    }

    private function reasonNotAdmitted(GraduationProcess $process): ?string
    {
        // UC-004 BR-002: A new version is admitted only in the phase and situation the rule lists.
        return match ($process->phase) {
            Phase::Requirements => 'Su trámite de titulación espera la asignación de su tutor.',
            Phase::ProfileEvaluation => $this->reasonInPhase($process, Phase::ProfileEvaluation),
            Phase::PreDefense => $this->reasonInPhase($process, Phase::PreDefense),
            Phase::FormalDefense => $this->reasonInFormalDefense($process),
            default => 'No tiene un trámite de titulación en curso.',
        };
    }

    /**
     * Profile evaluation and pre-defense: no version of the phase in review or approved.
     */
    private function reasonInPhase(GraduationProcess $process, Phase $phase): ?string
    {
        $versions = $process->projectVersions()->where('phase', $phase);

        $inReview = (clone $versions)->where('status', ProjectVersionStatus::InReview)->first();

        if ($inReview !== null) {
            return "Su versión {$inReview->number} del proyecto sigue en revisión.";
        }

        if ((clone $versions)->where('status', ProjectVersionStatus::Approved)->exists()) {
            return $phase === Phase::ProfileEvaluation
                ? 'Su perfil de proyecto ya fue aprobado y espera la asignación del tribunal evaluador.'
                : 'Su versión de la fase pre-defensa ya fue aprobada y espera el siguiente paso.';
        }

        return null;
    }

    private function reasonInFormalDefense(GraduationProcess $process): ?string
    {
        $defenses = $process->defenses()->where('is_held', true);

        // UC-004 BR-006: No version once the formal defense was held.
        if ((clone $defenses)->where('type', DefenseType::FormalDefense)->exists()) {
            return 'Su defensa formal ya se realizó.';
        }

        if ($process->defenses()->where('type', DefenseType::FormalDefense)
            ->where('scheduled_at', '<=', now())->exists()) {
            return 'Su defensa formal ya llegó a su hora de inicio.';
        }

        // UC-004 BR-006: Versions only when the held pre-defense left observations.
        $leftObservations = (clone $defenses)
            ->where('type', DefenseType::PreDefense)
            ->whereHas('observations')
            ->exists();

        return $leftObservations ? null : 'Su pre-defensa no dejó observaciones que subsanar.';
    }
}
