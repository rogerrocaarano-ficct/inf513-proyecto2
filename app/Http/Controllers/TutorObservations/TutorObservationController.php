<?php

namespace App\Http\Controllers\TutorObservations;

use App\Actions\TutorObservations\ListTutoredProcesses;
use App\Actions\TutorObservations\RegisterTutorObservations;
use App\Actions\TutorObservations\TutoredProcess;
use App\Http\Controllers\Controller;
use App\Http\Requests\TutorObservations\RegisterTutorObservationsRequest;
use App\Models\GraduationProcess;
use App\Models\Observation;
use App\Models\ProjectVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TutorObservationController extends Controller
{
    /**
     * Steps 1-2 and A2: the processes in progress the user tutors, those with an unobserved version first.
     */
    public function index(Request $request, ListTutoredProcesses $listProcesses): Response
    {
        return Inertia::render('tutor-observations/index', [
            'processes' => $listProcesses->handle($request->user())->map(fn (TutoredProcess $tutored): array => [
                'id' => $tutored->process->id,
                'student' => $tutored->process->student->user->name,
                'phase' => $tutored->process->phase->value,
                'has_unobserved_version' => $tutored->hasUnobservedVersion,
                'last_version' => $tutored->lastVersion === null ? null : [
                    'number' => $tutored->lastVersion->number,
                    'status' => $tutored->lastVersion->status->value,
                    'submitted_at' => $tutored->lastVersion->submitted_at->toIso8601String(),
                ],
            ])->values(),
        ]);
    }

    /**
     * Step 3: the history of project versions of a process the user tutors.
     */
    public function show(Request $request, GraduationProcess $graduationProcess): Response
    {
        // UC-007 BR-001: Only the current tutor of a process in progress opens its versions.
        Gate::authorize('viewAsTutor', $graduationProcess);

        $graduationProcess->load(['student.user', 'projectVersions.observations']);

        return Inertia::render('tutor-observations/show', [
            'process' => [
                'id' => $graduationProcess->id,
                'student' => $graduationProcess->student->user->name,
                'phase' => $graduationProcess->phase->value,
                'project_title' => $graduationProcess->project_title,
            ],
            'versions' => $graduationProcess->projectVersions->sortByDesc('number')->map(
                fn (ProjectVersion $version): array => [
                    'id' => $version->id,
                    'number' => $version->number,
                    'phase' => $version->phase->value,
                    'status' => $version->status->value,
                    'title' => $version->title,
                    'submitted_at' => $version->submitted_at->toIso8601String(),
                    'observations_count' => $version->observations->count(),
                    'observed_by_me' => $version->observations->contains('author_id', $request->user()->id),
                ],
            )->values(),
        ]);
    }

    /**
     * Step 4: the version to download with its phase, status and observations, and the form to observe it.
     */
    public function version(ProjectVersion $projectVersion): Response
    {
        // UC-007 BR-001, BR-002: The current tutor observes any version of a process in progress.
        Gate::authorize('observe', $projectVersion);

        $projectVersion->load(['graduationProcess.student.user', 'observations.author']);

        return Inertia::render('tutor-observations/version', [
            'version' => [
                'id' => $projectVersion->id,
                'process_id' => $projectVersion->graduation_process_id,
                'student' => $projectVersion->graduationProcess->student->user->name,
                'number' => $projectVersion->number,
                'phase' => $projectVersion->phase->value,
                'status' => $projectVersion->status->value,
                'title' => $projectVersion->title,
                'file_name' => $projectVersion->original_file_name,
                'file_size_bytes' => $projectVersion->file_size_bytes,
                'submitted_at' => $projectVersion->submitted_at->toIso8601String(),
            ],
            'observations' => $projectVersion->observations->map(fn (Observation $observation): array => [
                'id' => $observation->id,
                'author' => $observation->author->name,
                'body' => $observation->body,
                'recorded_at' => $observation->recorded_at->toIso8601String(),
            ])->values(),
        ]);
    }

    /**
     * Steps 5-9 and A1, A4: register the observations of the tutor on the version.
     */
    public function store(
        RegisterTutorObservationsRequest $request,
        ProjectVersion $projectVersion,
        RegisterTutorObservations $registerObservations,
    ): RedirectResponse {
        $recorded = $registerObservations->handle($projectVersion, $request->observationTexts(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => count($recorded) === 1 ? 'Observación registrada.' : 'Observaciones registradas.',
        ]);

        return to_route('tutor-observations.version', $projectVersion);
    }

    /**
     * Downloads the file of a project version of a process the user tutors.
     */
    public function download(ProjectVersion $projectVersion): StreamedResponse
    {
        Gate::authorize('download', $projectVersion);

        return Storage::disk('local')->download($projectVersion->file_path, $projectVersion->original_file_name);
    }
}
