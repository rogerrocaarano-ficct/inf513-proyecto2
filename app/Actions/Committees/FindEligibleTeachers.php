<?php

namespace App\Actions\Committees;

use App\Enums\RoleName;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Finds the teachers who can be members of the evaluation committee of a graduation process.
 */
class FindEligibleTeachers
{
    /**
     * @return Builder<Teacher>
     */
    public function query(GraduationProcess $process): Builder
    {
        // UC-006 BR-001: Eligible: active teacher, not the career director, not the tutor, not ever a member.
        return Teacher::query()
            ->where('is_active', true)
            ->whereDoesntHave('user.roles', fn (Builder $query) => $query->where('name', RoleName::CareerDirector))
            ->when($process->tutor_id, fn (Builder $query, int $tutorId) => $query->whereKeyNot($tutorId))
            ->whereDoesntHave('committeeMemberships', fn (Builder $query) => $query
                ->where('graduation_process_id', $process->getKey()));
    }

    /**
     * @return Collection<int, Teacher>
     */
    public function handle(GraduationProcess $process): Collection
    {
        return $this->query($process)
            ->with('user')
            ->get()
            ->sortBy(fn (Teacher $teacher): string => $teacher->user->name)
            ->values();
    }
}
