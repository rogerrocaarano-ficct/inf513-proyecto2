<?php

namespace App\Http\Controllers\ProjectProfiles;

use App\Actions\ProjectProfiles\EvaluateProjectProfile;
use App\Actions\ProjectProfiles\ListProfilesForReview;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectProfiles\EvaluateProjectProfileRequest;
use App\Models\Observation;
use App\Models\ProjectVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectProfileController extends Controller
{
    /**
     * Step 2 and A3: the project profiles in review of the reviewer, the oldest first.
     */
    public function index(Request $request, ListProfilesForReview $listProfiles): Response
    {
        return Inertia::render('project-profiles/index', [
            'profiles' => $listProfiles->handle($request->user())->map(fn (ProjectVersion $profile): array => [
                'id' => $profile->id,
                'student' => $profile->graduationProcess->student->user->name,
                'title' => $profile->title,
                'number' => $profile->number,
                'submitted_at' => $profile->submitted_at->toIso8601String(),
            ])->values(),
        ]);
    }

    /**
     * Step 4 and BR-005: the profile to download and the earlier rejected profiles with their observations.
     */
    public function show(ProjectVersion $projectVersion): Response|RedirectResponse
    {
        $profile = $this->profileOrFail($projectVersion);

        Gate::authorize('evaluate', $profile);

        // UC-005 BR-003: An evaluated profile is not evaluated again.
        if ($profile->status !== ProjectVersionStatus::InReview) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'El perfil de proyecto ya fue evaluado.',
            ]);

            return to_route('project-profiles.index');
        }

        $profile->load('graduationProcess.student.user');

        // UC-005 BR-005: The rejected profiles before this one, with their observations, to compare them.
        $previous = ProjectVersion::query()
            ->profiles()
            ->where('graduation_process_id', $profile->graduation_process_id)
            ->where('status', ProjectVersionStatus::Rejected)
            ->where('number', '<', $profile->number)
            ->with('observations.author')
            ->orderBy('number')
            ->get();

        return Inertia::render('project-profiles/show', [
            'profile' => [
                'id' => $profile->id,
                'student' => $profile->graduationProcess->student->user->name,
                'title' => $profile->title,
                'number' => $profile->number,
                'file_name' => $profile->original_file_name,
                'file_size_bytes' => $profile->file_size_bytes,
                'submitted_at' => $profile->submitted_at->toIso8601String(),
            ],
            'previousProfiles' => $previous->map(fn (ProjectVersion $rejected): array => [
                'id' => $rejected->id,
                'number' => $rejected->number,
                'title' => $rejected->title,
                'file_name' => $rejected->original_file_name,
                'submitted_at' => $rejected->submitted_at->toIso8601String(),
                'evaluated_at' => $rejected->evaluated_at?->toIso8601String(),
                'observations' => $rejected->observations->map(fn (Observation $observation): array => [
                    'id' => $observation->id,
                    'author' => $observation->author->name,
                    'body' => $observation->body,
                    'recorded_at' => $observation->recorded_at->toIso8601String(),
                ])->values(),
            ])->values(),
        ]);
    }

    /**
     * Steps 5-9 and A1, A2: approve or reject the project profile.
     */
    public function update(
        EvaluateProjectProfileRequest $request,
        ProjectVersion $projectVersion,
        EvaluateProjectProfile $evaluateProjectProfile,
    ): RedirectResponse {
        // UC-005 BR-001: The request authorizes the reviewer before validating.
        $profile = $this->profileOrFail($projectVersion);

        $result = $request->result();

        $evaluateProjectProfile->handle($profile, $result, $request->observationTexts(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $result === ProjectVersionStatus::Approved
                ? 'Perfil de proyecto aprobado.'
                : 'Perfil de proyecto rechazado.',
        ]);

        return to_route('project-profiles.index');
    }

    /**
     * Downloads the file of a project profile.
     */
    public function download(ProjectVersion $projectVersion): StreamedResponse
    {
        $profile = $this->profileOrFail($projectVersion);

        Gate::authorize('download', $profile);

        return Storage::disk('local')->download($profile->file_path, $profile->original_file_name);
    }

    private function profileOrFail(ProjectVersion $projectVersion): ProjectVersion
    {
        abort_unless($projectVersion->phase === Phase::ProfileEvaluation, 404);

        return $projectVersion;
    }
}
