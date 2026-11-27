<?php

namespace App\Actions\Defenses;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\Defense;
use App\Models\ProjectVersion;

/** Resolves the version defended under UC-004 BR-006. */
class FindFormalDefenseVersion
{
    public function handle(Defense $defense): ?ProjectVersion
    {
        $process = $defense->graduationProcess;
        $hadObservations = $process->defenses()->where('type', DefenseType::PreDefense)
            ->where('is_held', true)->whereHas('observations')->exists();

        if ($hadObservations) {
            $version = $process->projectVersions()
                ->where('phase', Phase::FormalDefense)
                ->where('status', ProjectVersionStatus::Submitted)
                ->where('submitted_at', '<=', $defense->scheduled_at)
                ->reorder('number', 'desc')
                ->first();

            if ($version !== null) {
                return $version;
            }
        }

        return $process->projectVersions()->where('phase', Phase::PreDefense)
            ->where('status', ProjectVersionStatus::Approved)
            ->reorder('number', 'desc')->first();
    }
}
