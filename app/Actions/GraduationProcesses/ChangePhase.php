<?php

namespace App\Actions\GraduationProcesses;

use App\Enums\Phase;
use App\Models\GraduationProcess;
use App\Models\PhaseChange;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Moves a graduation process into a phase and records when and by whose action it entered it.
 */
class ChangePhase
{
    public function handle(GraduationProcess $process, Phase $phase, User $changedBy, ?CarbonInterface $enteredAt = null): PhaseChange
    {
        $enteredAt ??= now();

        $process->forceFill(['phase' => $phase])->save();

        return PhaseChange::query()->create([
            'graduation_process_id' => $process->id,
            'phase' => $phase,
            'entered_at' => $enteredAt,
            'changed_by' => $changedBy->id,
        ]);
    }
}
