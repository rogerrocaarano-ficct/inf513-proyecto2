<?php

namespace App\Actions\Defenses;

use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Finds the pre-defenses and formal defenses of other processes that a teacher takes part in and that overlap
 * a pending act of the given process, as when the teacher replaces its tutor or a committee member.
 */
class FindScheduleConflicts
{
    /**
     * @return Collection<int, array{defense: Defense, conflictsWith: Defense}>
     */
    public function handle(Teacher $teacher, GraduationProcess $process): Collection
    {
        $pendingActs = $process->defenses()->pending()->get();

        if ($pendingActs->isEmpty()) {
            return collect();
        }

        $actsOfTeacher = Defense::query()
            ->pending()
            ->whereKeyNot($pendingActs->modelKeys())
            ->whereHas('graduationProcess', fn (Builder $query) => $query
                ->whereKeyNot($process->id)
                // UC-009 BR-003: A teacher takes part as tutor or as a current committee member.
                ->where(fn (Builder $query) => $query
                    ->where('tutor_id', $teacher->id)
                    ->orWhereHas('committeeMembers', fn (Builder $query) => $query
                        ->where('teacher_id', $teacher->id)
                        ->whereNull('replaced_at'))))
            ->with('graduationProcess.student.user')
            ->get();

        return $pendingActs->flatMap(fn (Defense $act) => $actsOfTeacher
            ->filter(fn (Defense $other): bool => $this->overlap($act, $other))
            ->map(fn (Defense $other): array => ['defense' => $act, 'conflictsWith' => $other]))
            ->values();
    }

    /**
     * UC-009 BR-003: Each act lasts two hours; two acts overlap if each starts before the other ends.
     */
    private function overlap(Defense $first, Defense $second): bool
    {
        return $first->scheduled_at->lt($second->scheduled_at->addHours(Defense::DURATION_HOURS))
            && $second->scheduled_at->lt($first->scheduled_at->addHours(Defense::DURATION_HOURS));
    }
}
