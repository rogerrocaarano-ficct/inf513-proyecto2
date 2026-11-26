<?php

namespace App\Actions\Committees;

use App\Actions\GraduationProcesses\ChangePhase;
use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Assigns the three teachers of the evaluation committee and moves the process to the pre-defense phase.
 */
class AssignCommittee
{
    public function __construct(
        private ListProcessesForCommittee $listProcesses,
        private FindEligibleTeachers $findEligibleTeachers,
        private ChangePhase $changePhase,
        private NotifyUser $notifyUser,
    ) {}

    /**
     * @param  array<int, int|string>  $teacherIds
     * @return Collection<int, Teacher> The three members, in the order chosen
     *
     * @throws ValidationException
     */
    public function handle(GraduationProcess $process, array $teacherIds, User $assignedBy): Collection
    {
        return DB::transaction(function () use ($process, $teacherIds, $assignedBy): Collection {
            $process = GraduationProcess::query()->whereKey($process->getKey())->lockForUpdate()->firstOrFail();

            $this->ensureAssignable($process);

            $teachers = $this->ensureEligible($process, $this->ensureThreeDistinctTeachers($teacherIds));

            $assignedAt = now();

            foreach ($teachers as $teacher) {
                CommitteeMember::query()->create([
                    'graduation_process_id' => $process->id,
                    'teacher_id' => $teacher->id,
                    'assigned_at' => $assignedAt,
                ]);
            }

            // UC-006 BR-003: The assignment moves the process from profile evaluation to the pre-defense phase.
            $this->changePhase->handle($process, Phase::PreDefense, $assignedBy, $assignedAt);

            $this->notify($process, $teachers);

            return $teachers;
        });
    }

    private function ensureAssignable(GraduationProcess $process): void
    {
        // UC-006 BR-003: Assign only to a process in progress, in profile evaluation, with an approved profile.
        $isAssignable = $process->phase === Phase::ProfileEvaluation
            && $this->listProcesses->query()->whereKey($process->getKey())->exists()
            && ! $process->committeeMembers()->exists();

        if (! $isAssignable) {
            throw ValidationException::withMessages([
                'graduation_process' => 'El tribunal evaluador solo se asigna a un trámite de titulación en curso, en la fase evaluación de perfil y con su perfil de proyecto aprobado.',
            ]);
        }
    }

    /**
     * @param  array<int, int|string>  $teacherIds
     * @return list<int>
     *
     * @throws ValidationException
     */
    private function ensureThreeDistinctTeachers(array $teacherIds): array
    {
        $ids = array_map(intval(...), $teacherIds);

        // UC-006 BR-002: The committee is exactly three distinct teachers (C-006).
        if (count($ids) !== 3 || count(array_unique($ids)) !== 3) {
            throw ValidationException::withMessages([
                'teacher_ids' => 'El tribunal evaluador se compone de exactamente tres docentes distintos.',
            ]);
        }

        return array_values($ids);
    }

    /**
     * @param  list<int>  $teacherIds
     * @return Collection<int, Teacher>
     *
     * @throws ValidationException
     */
    private function ensureEligible(GraduationProcess $process, array $teacherIds): Collection
    {
        $eligible = $this->findEligibleTeachers->query($process)->with('user')->whereKey($teacherIds)->get()->keyBy('id');

        // UC-006 BR-001: Every chosen teacher must be eligible as a committee member of the process.
        if ($eligible->count() !== 3) {
            throw ValidationException::withMessages([
                'teacher_ids' => 'Alguno de los docentes elegidos no es elegible como miembro del tribunal evaluador.',
            ]);
        }

        return collect($teacherIds)->map(fn (int $id): Teacher => $eligible[$id])->values();
    }

    /**
     * @param  Collection<int, Teacher>  $teachers
     */
    private function notify(GraduationProcess $process, Collection $teachers): void
    {
        $studentName = $process->student->user->name;
        [$first, $second, $third] = $teachers->map(fn (Teacher $teacher): string => $teacher->user->name)->all();

        foreach ($teachers as $teacher) {
            $this->notifyUser->handle(
                $teacher->user,
                "Se le asignó como miembro del tribunal evaluador del trámite de titulación de {$studentName}.",
                $process,
            );
        }

        $this->notifyUser->handle(
            $process->student->user,
            "Su trámite de titulación pasó a la fase pre-defensa. Su tribunal evaluador: {$first}, {$second} y {$third}.",
            $process,
        );
    }
}
