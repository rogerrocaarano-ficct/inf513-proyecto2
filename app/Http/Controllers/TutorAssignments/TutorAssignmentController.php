<?php

namespace App\Http\Controllers\TutorAssignments;

use App\Actions\TutorAssignments\AssignTutor;
use App\Http\Controllers\Controller;
use App\Http\Requests\TutorAssignments\AssignTutorRequest;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TutorAssignmentController extends Controller
{
    /**
     * List the processes in progress, those without a tutor first (UC-002 step 2).
     */
    public function index(): Response
    {
        $processes = GraduationProcess::query()
            ->inProgress()
            ->with(['student.user', 'tutor.user'])
            ->orderByRaw('tutor_id is not null')
            ->orderBy('started_at')
            ->get()
            ->map(fn (GraduationProcess $process): array => [
                'id' => $process->id,
                'student' => $process->student->user->name,
                'phase' => $process->phase->value,
                'tutor' => $process->tutor?->user->name,
                'proposedTutor' => $process->proposed_tutor_name,
            ]);

        return Inertia::render('tutor-assignments/index', ['processes' => $processes]);
    }

    /**
     * Show the eligible teachers of a process in progress (UC-002 step 4 and A1 step 1).
     */
    public function edit(GraduationProcess $graduationProcess): Response
    {
        // UC-002 BR-002: The tutor is assigned or changed only in a process in progress.
        abort_unless(GraduationProcess::query()->inProgress()->whereKey($graduationProcess->id)->exists(), 404);

        $graduationProcess->load(['student.user', 'tutor.user']);

        $teachers = Teacher::query()
            ->eligibleAsTutor($graduationProcess)
            ->with('user')
            ->get()
            ->sortBy(fn (Teacher $teacher): string => mb_strtolower($teacher->user->name))
            ->map(fn (Teacher $teacher): array => ['id' => $teacher->id, 'name' => $teacher->user->name])
            ->values();

        return Inertia::render('tutor-assignments/edit', [
            'process' => [
                'id' => $graduationProcess->id,
                'student' => $graduationProcess->student->user->name,
                'phase' => $graduationProcess->phase->value,
                'tutor' => $graduationProcess->tutor?->user->name,
                'proposedTutor' => $graduationProcess->proposed_tutor_name,
            ],
            'teachers' => $teachers,
        ]);
    }

    /**
     * Assign or change the tutor (UC-002 steps 5-9 and A1).
     */
    public function update(AssignTutorRequest $request, GraduationProcess $graduationProcess, AssignTutor $assignTutor): RedirectResponse
    {
        // UC-002 BR-002: The tutor is assigned or changed only in a process in progress.
        abort_unless(GraduationProcess::query()->inProgress()->whereKey($graduationProcess->id)->exists(), 404);

        $teacher = Teacher::query()->with('user')->findOrFail($request->integer('teacher_id'));

        $process = $assignTutor->handle($graduationProcess, $teacher, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Tutor asignado: {$teacher->user->name}. Fase: {$process->phase->value}.",
        ]);

        return to_route('tutor-assignments.index');
    }
}
