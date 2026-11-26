<?php

namespace App\Actions\Committees;

use App\Actions\Defenses\FindScheduleConflicts;
use App\Actions\Notifications\NotifyUser;
use App\Enums\DefenseType;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Replaces one member of the evaluation committee with another teacher, keeping the phase and the scheduled acts.
 */
class ReplaceCommitteeMember
{
    public function __construct(
        private ListProcessesForCommittee $listProcesses,
        private FindEligibleTeachers $findEligibleTeachers,
        private FindScheduleConflicts $findScheduleConflicts,
        private NotifyUser $notifyUser,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(GraduationProcess $process, int $replacedMemberId, int $newTeacherId): CommitteeMember
    {
        return DB::transaction(function () use ($process, $replacedMemberId, $newTeacherId): CommitteeMember {
            $this->findScheduleConflicts->lockCalendar();
            $process = GraduationProcess::query()->whereKey($process->getKey())->lockForUpdate()->firstOrFail();

            $this->ensureReplaceable($process);

            $replaced = $process->currentCommitteeMembers()->with('teacher.user')->find($replacedMemberId);

            if ($replaced === null) {
                throw ValidationException::withMessages([
                    'committee_member_id' => 'El miembro que se reemplaza no es un miembro vigente del tribunal evaluador.',
                ]);
            }

            $newTeacher = $this->findEligibleTeachers->query($process)->with('user')->find($newTeacherId);

            // UC-006 BR-001: The teacher who enters must be eligible as a committee member of the process.
            if ($newTeacher === null) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'El docente elegido no es elegible como miembro del tribunal evaluador.',
                ]);
            }

            $this->ensureWithoutScheduleConflict($newTeacher, $process);

            // UC-006 BR-004: One member is replaced; the phase and the scheduled acts do not change.
            $now = now();
            $replaced->forceFill(['replaced_at' => $now])->save();
            $member = CommitteeMember::query()->create([
                'graduation_process_id' => $process->id,
                'teacher_id' => $newTeacher->id,
                'assigned_at' => $now,
            ]);

            $this->notify($process, $replaced->teacher, $newTeacher);

            return $member;
        });
    }

    private function ensureReplaceable(GraduationProcess $process): void
    {
        // UC-006 BR-004: A member is replaced only in a process in progress whose formal defense was not held.
        if ($process->defenses()->where('type', DefenseType::FormalDefense)->where('is_held', true)->exists()) {
            throw ValidationException::withMessages([
                'graduation_process' => 'El tribunal evaluador ya no se puede cambiar porque la defensa formal se realizó.',
            ]);
        }

        if (! $this->listProcesses->query()->whereKey($process->getKey())->exists()
            || ! $process->currentCommitteeMembers()->exists()) {
            throw ValidationException::withMessages([
                'graduation_process' => 'El tribunal evaluador solo se reemplaza en un trámite de titulación en curso que ya lo tiene.',
            ]);
        }
    }

    private function ensureWithoutScheduleConflict(Teacher $newTeacher, GraduationProcess $process): void
    {
        // UC-006 BR-006: A replacement is not confirmed if the new teacher has a schedule conflict (UC-009 BR-003).
        $conflicts = $this->findScheduleConflicts->handle($newTeacher, $process);

        if ($conflicts->isEmpty()) {
            return;
        }

        $sentences = $conflicts->sortBy(fn (array $conflict) => $conflict['conflictsWith']->scheduled_at)->map(function (array $conflict) use ($newTeacher): string {
            $other = $conflict['conflictsWith'];
            $otherAct = mb_strtolower($other->type->value);
            $thisAct = mb_strtolower($conflict['defense']->type->value);
            $student = $other->graduationProcess->student->user->name;
            $when = $other->scheduled_at->setTimezone(FindScheduleConflicts::TIMEZONE)->format('d/m/Y H:i');

            return "{$newTeacher->user->name} tiene la {$otherAct} de {$student} el {$when}, que se cruza con la {$thisAct} de este trámite. Reprograme antes el acto o elija otro docente.";
        });

        throw ValidationException::withMessages(['teacher_id' => $sentences->implode(' ')]);
    }

    private function notify(GraduationProcess $process, Teacher $replaced, Teacher $new): void
    {
        $studentName = $process->student->user->name;

        $this->notifyUser->handle(
            $new->user,
            "Se le asignó como miembro del tribunal evaluador del trámite de titulación de {$studentName}.",
            $process,
        );
        $this->notifyUser->handle(
            $replaced->user,
            "Dejó de ser miembro del tribunal evaluador del trámite de titulación de {$studentName}.",
            $process,
        );
        $this->notifyUser->handle(
            $process->student->user,
            "Su tribunal evaluador tiene un nuevo miembro: {$new->user->name}, en lugar de {$replaced->user->name}.",
            $process,
        );
    }
}
