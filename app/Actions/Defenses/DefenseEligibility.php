<?php

namespace App\Actions\Defenses;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\Defense;
use App\Models\GraduationProcess;
use Illuminate\Database\Eloquent\Builder;

class DefenseEligibility
{
    /** @return Builder<GraduationProcess> */
    public function query(DefenseType $type): Builder
    {
        $query = GraduationProcess::query()->inProgress()
            ->whereNotNull('tutor_id')
            ->has('currentCommitteeMembers', '=', 3)
            ->whereDoesntHave('defenses', fn (Builder $acts) => $acts->pending()->where('type', $type));

        // UC-008 BR-003/BR-004: Only approvals by current members of this committee count.
        return match ($type) {
            DefenseType::PreDefense => $query->where('phase', Phase::PreDefense)
                ->whereHas('projectVersions', fn (Builder $versions) => $versions
                    ->where('phase', Phase::PreDefense)->where('status', ProjectVersionStatus::Approved)
                    ->whereHas('approvers', fn (Builder $members) => $members->current()
                        ->whereColumn('committee_members.graduation_process_id', 'project_versions.graduation_process_id'), '=', 3)),
            DefenseType::FormalDefense => $query->where('phase', Phase::FormalDefense)
                ->whereHas('defenses', fn (Builder $acts) => $acts
                    ->where('type', DefenseType::PreDefense)->where('is_held', true))
                ->whereDoesntHave('defenses', fn (Builder $acts) => $acts
                    ->where('type', DefenseType::FormalDefense)->where('is_held', true)),
        };
    }

    public function canReschedule(Defense $defense, GraduationProcess $process): bool
    {
        return ! $defense->is_held && $process->concluded_at === null && $process->abandoned_at === null;
    }
}
