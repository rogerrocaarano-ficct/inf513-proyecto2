<?php

namespace App\Actions\TutorAssignments;

use App\Actions\Defenses\FindScheduleConflicts;
use App\Actions\GraduationProcesses\ChangePhase;
use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Leaves a teacher as the tutor of a graduation process in progress: assigns the first tutor and moves the
 * process to the profile evaluation phase (UC-002 steps 7-8), or replaces the current tutor (UC-002 A1).
 */
class AssignTutor
{
    public function __construct(
        private readonly ChangePhase $changePhase,
        private readonly NotifyUser $notifyUser,
        private readonly FindScheduleConflicts $findScheduleConflicts,
    ) {}

    /**
     * @throws ValidationException when the teacher is not eligible or the new tutor has a schedule conflict
     */
    public function handle(GraduationProcess $process, Teacher $teacher, User $assignedBy): GraduationProcess
    {
        return DB::transaction(function () use ($process, $teacher, $assignedBy): GraduationProcess {
            $process = GraduationProcess::query()->lockForUpdate()->findOrFail($process->id);

            // UC-002 BR-002: The tutor is assigned or changed only in a process in progress.
            if (! GraduationProcess::query()->inProgress()->whereKey($process->id)->exists()) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Solo se asigna tutor en un trámite de titulación en curso.',
                ]);
            }

            // UC-002 BR-001: Eligible tutor: active, not the workshop teacher, not a committee member, not its tutor.
            if (! Teacher::query()->eligibleAsTutor($process)->whereKey($teacher->id)->exists()) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'El docente elegido no es elegible como tutor de este trámite de titulación.',
                ]);
            }

            $teacher->loadMissing('user');
            $process->loadMissing('student.user');
            $student = $process->student->user;

            if ($process->tutor_id === null) {
                $this->assignFirstTutor($process, $teacher, $assignedBy);

                $this->notifyUser->handle(
                    $teacher->user,
                    "Se le asignó como tutor del trámite de titulación de {$student->name}.",
                    $process,
                );
                $this->notifyUser->handle(
                    $student,
                    "Su trámite de titulación pasó a la fase evaluación de perfil con {$teacher->user->name} como tutor.",
                    $process,
                );
            } else {
                $this->replaceTutor($process, $teacher);

                // UC-002 BR-005: The new tutor and the student are notified; the previous tutor is not.
                $this->notifyUser->handle(
                    $teacher->user,
                    "Se le asignó como tutor del trámite de titulación de {$student->name}.",
                    $process,
                );
                $this->notifyUser->handle(
                    $student,
                    "Su trámite de titulación tiene un nuevo tutor: {$teacher->user->name}.",
                    $process,
                );
            }

            return $process->refresh();
        });
    }

    private function assignFirstTutor(GraduationProcess $process, Teacher $teacher, User $assignedBy): void
    {
        $process->forceFill(['tutor_id' => $teacher->id])->save();

        // UC-002 BR-003: Assigning the tutor moves a process in the requirements phase to profile evaluation.
        $this->changePhase->handle($process, Phase::ProfileEvaluation, $assignedBy);
    }

    /**
     * @throws ValidationException
     */
    private function replaceTutor(GraduationProcess $process, Teacher $teacher): void
    {
        // UC-002 BR-006: A change of tutor is refused when the new tutor has a schedule conflict (UC-009 BR-003).
        $conflicts = $this->findScheduleConflicts->handle($teacher, $process);

        if ($conflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'teacher_id' => $conflicts->map(fn (array $conflict): string => sprintf(
                    '%s tiene la %s de %s el %s, que se cruza con la %s de este trámite. '
                    .'Reprograme antes el acto o elija otro docente.',
                    $teacher->user->name,
                    mb_strtolower($conflict['conflictsWith']->type->value),
                    $conflict['conflictsWith']->graduationProcess->student->user->name,
                    $conflict['conflictsWith']->scheduled_at->format('d/m/Y H:i'),
                    mb_strtolower($conflict['defense']->type->value),
                ))->unique()->implode(' '),
            ]);
        }

        // UC-002 BR-003: Changing the tutor does not change the phase.
        // UC-002 BR-006: Without a conflict the schedule of the acts is kept.
        $process->forceFill(['tutor_id' => $teacher->id])->save();
    }
}
