<?php

namespace App\Actions\Accounts;

use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Finds what a teacher cannot leave while an account is deactivated or loses the teacher role.
 */
class FindTeacherAssignments
{
    /**
     * @return list<array{student: string|null, role: string}>
     */
    public function handle(Teacher $teacher): array
    {
        return $this->handleMany(collect([$teacher]))[$teacher->getKey()];
    }

    /**
     * The assignments of several teachers in a bounded number of queries.
     *
     * @param  Collection<int, Teacher>  $teachers
     * @return array<int, list<array{student: string|null, role: string}>> Keyed by teacher id.
     */
    public function handleMany(Collection $teachers): array
    {
        $ids = $teachers->pluck('id')->all();
        $assignments = array_fill_keys($ids, []);

        // UC-018 BR-003: Tutor of a graduation process in progress.
        GraduationProcess::query()->inProgress()->whereIn('tutor_id', $ids)->with('student.user')->get()
            ->each(function (GraduationProcess $process) use (&$assignments): void {
                $assignments[$process->tutor_id][] = ['student' => $process->student->user->name, 'role' => 'tutor'];
            });

        // UC-018 BR-003: Current committee member of a graduation process in progress.
        CommitteeMember::query()
            ->whereIn('teacher_id', $ids)
            ->whereNull('replaced_at')
            ->whereHas('graduationProcess', fn (Builder $query) => $query->inProgress())
            ->with('graduationProcess.student.user')
            ->get()
            ->each(function (CommitteeMember $member) use (&$assignments): void {
                $assignments[$member->teacher_id][] = [
                    'student' => $member->graduationProcess->student->user->name,
                    'role' => 'miembro del tribunal',
                ];
            });

        // UC-018 BR-003: The workshop teacher.
        foreach ($teachers->where('is_workshop_teacher', true) as $teacher) {
            $assignments[$teacher->getKey()][] = ['student' => null, 'role' => 'docente de taller de grado'];
        }

        return $assignments;
    }
}
