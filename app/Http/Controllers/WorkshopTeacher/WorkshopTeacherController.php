<?php

namespace App\Http\Controllers\WorkshopTeacher;

use App\Actions\WorkshopTeacher\DesignateWorkshopTeacher;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkshopTeacher\DesignateWorkshopTeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopTeacherController extends Controller
{
    /**
     * Show the current workshop teacher and the other active teachers (UC-003 step 2).
     */
    public function edit(DesignateWorkshopTeacher $designate): Response
    {
        $current = Teacher::query()->with('user')->workshopTeacher()->first();

        return Inertia::render('workshop-teacher/edit', [
            'current' => $current === null ? null : ['id' => $current->id, 'name' => $current->user->name],
            // UC-003 BR-002: Only an active teacher who is not already the workshop teacher can be chosen.
            'candidates' => Teacher::query()
                ->with('user')
                ->active()
                ->where('is_workshop_teacher', false)
                ->get()
                ->map(fn (Teacher $teacher): array => ['id' => $teacher->id, 'name' => $teacher->user->name])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all(),
            // OQ-050: Preview of the profiles that will pass to the new reviewer, from the query the Action uses.
            'profilesInReview' => $designate->profilesToReassign($current)->count(),
        ]);
    }

    /**
     * Designate the chosen teacher (UC-003 steps 4 to 8).
     */
    public function update(DesignateWorkshopTeacherRequest $request, DesignateWorkshopTeacher $designate): RedirectResponse
    {
        $result = $designate->handle(Teacher::query()->whereKey($request->validated('teacher_id'))->firstOrFail());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$result['teacher']->user->name} es el docente de taller de grado; "
                ."{$result['profilesMoved']} perfiles de proyecto pasaron a su revisión.",
        ]);

        return to_route('workshop-teacher.edit');
    }
}
