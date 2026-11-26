<?php

namespace App\Actions\Defenses;

use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Finds the pre-defenses and formal defenses of other processes that a teacher takes part in and that overlap
 * a pending act of the given process, as when the teacher replaces its tutor or a committee member.
 */
class FindScheduleConflicts
{
    public const string TIMEZONE = 'America/La_Paz';

    /**
     * Serialize calendar writes and participant replacements before acquiring any process row locks.
     * A concurrent operation can introduce a conflict even when there are no matching rows yet.
     */
    public function lockCalendar(): void
    {
        DB::select('select pg_advisory_xact_lock(513, 9)');
    }

    /**
     * UC-009 BR-003: Each act lasts two hours. An act ending at the new start does not conflict.
     *
     * @return Builder<Defense>
     */
    public function overlapping(CarbonInterface $start): Builder
    {
        return Defense::query()->pending()
            ->where('scheduled_at', '>', $start->copy()->subHours(Defense::DURATION_HOURS))
            ->where('scheduled_at', '<', $start->copy()->addHours(Defense::DURATION_HOURS));
    }

    /**
     * @return Collection<int, array{defense: Defense, conflictsWith: Defense}>
     */
    public function handle(Teacher $teacher, GraduationProcess $process): Collection
    {
        $pendingActs = $process->defenses()->pending()->get();

        if ($pendingActs->isEmpty()) {
            return collect();
        }

        return $pendingActs->flatMap(fn (Defense $act) => $this->overlapping($act->scheduled_at)
            ->whereHas('graduationProcess', fn (Builder $query) => $query
                ->whereKeyNot($process->id)
                // UC-009 BR-003: A teacher takes part as tutor or as a current committee member.
                ->where(fn (Builder $query) => $query
                    ->where('tutor_id', $teacher->id)
                    ->orWhereHas('committeeMembers', fn (Builder $query) => $query
                        ->where('teacher_id', $teacher->id)
                        ->whereNull('replaced_at'))))
            ->with('graduationProcess.student.user')
            ->get()
            ->map(fn (Defense $other): array => ['defense' => $act, 'conflictsWith' => $other]))
            ->values();
    }
}
