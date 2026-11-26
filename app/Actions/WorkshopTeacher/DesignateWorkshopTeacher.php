<?php

namespace App\Actions\WorkshopTeacher;

use App\Actions\Notifications\NotifyUser;
use App\Enums\ProjectVersionStatus;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Designates the workshop teacher, takes the designation from the previous one and passes the project profiles in
 * review to the new reviewer (UC-003).
 */
class DesignateWorkshopTeacher
{
    public function __construct(private NotifyUser $notifyUser) {}

    /**
     * Project profiles in review that pass to the new workshop teacher: the previous teacher's and the unassigned.
     *
     * @return Builder<ProjectVersion>
     */
    public function profilesToReassign(?Teacher $previous): Builder
    {
        // UC-003 BR-003: Profiles in review of the previous teacher, or without reviewer, pass to the new one.
        return ProjectVersion::query()
            ->profiles()
            ->where('status', ProjectVersionStatus::InReview)
            ->where(fn (Builder $query) => $query
                ->whereNull('reviewer_id')
                ->when($previous, fn (Builder $query) => $query->orWhere('reviewer_id', $previous->getKey())));
    }

    /**
     * @return array{teacher: Teacher, previous: ?Teacher, profilesMoved: int}
     *
     * @throws ValidationException
     */
    public function handle(Teacher $teacher): array
    {
        return DB::transaction(function () use ($teacher): array {
            $teacher = Teacher::query()->with('user')->whereKey($teacher->getKey())->lockForUpdate()->firstOrFail();

            // UC-003 BR-002: Only an active teacher who is not already the workshop teacher can be designated.
            if (! $teacher->is_active || $teacher->is_workshop_teacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'El docente elegido no puede ser designado como docente de taller de grado.',
                ]);
            }

            $previous = Teacher::query()->with('user')->workshopTeacher()->lockForUpdate()->first();

            // UC-003 BR-001: At most one workshop teacher; clear the previous flag before setting the new one.
            $previous?->forceFill(['is_workshop_teacher' => false])->save();
            $teacher->forceFill(['is_workshop_teacher' => true])->save();

            $profilesMoved = $this->profilesToReassign($previous)->update(['reviewer_id' => $teacher->getKey()]);

            $this->notifyUser->handle(
                $teacher->user,
                "Se le designó como docente de taller de grado; recibe {$profilesMoved} perfiles de proyecto para revisar.",
            );

            if ($previous !== null) {
                $this->notifyUser->handle(
                    $previous->user,
                    "Dejó de ser docente de taller de grado; sus perfiles de proyecto en revisión pasaron a {$teacher->user->name}.",
                );
            }

            return ['teacher' => $teacher, 'previous' => $previous, 'profilesMoved' => $profilesMoved];
        });
    }
}
