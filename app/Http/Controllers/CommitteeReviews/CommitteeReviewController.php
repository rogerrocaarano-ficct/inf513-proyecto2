<?php

namespace App\Http\Controllers\CommitteeReviews;

use App\Actions\CommitteeReviews\ListProcessesForReview;
use App\Actions\CommitteeReviews\RegisterCommitteePronouncement;
use App\Actions\CommitteeReviews\ReviewProcess;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommitteeReviews\RegisterCommitteePronouncementRequest;
use App\Models\CommitteeMember;
use App\Models\Observation;
use App\Models\ProjectVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommitteeReviewController extends Controller
{
    /**
     * Steps 1-2 and A5: the processes of the user's committee, pending reviews first.
     */
    public function index(Request $request, ListProcessesForReview $listProcesses): Response
    {
        return Inertia::render('committee-reviews/index', [
            'processes' => $listProcesses->handle($request->user())->map(fn (ReviewProcess $item): array => [
                'id' => $item->process->id,
                'student' => $item->process->student->user->name,
                'project_title' => $item->process->project_title,
                'phase' => $item->process->phase->value,
                'pending_version' => $item->pendingVersion === null ? null : [
                    'id' => $item->pendingVersion->id,
                    'number' => $item->pendingVersion->number,
                    'submitted_at' => $item->pendingVersion->submitted_at->toIso8601String(),
                ],
                'last_version' => $item->lastVersion === null ? null : [
                    'id' => $item->lastVersion->id,
                    'number' => $item->lastVersion->number,
                    'phase' => $item->lastVersion->phase->value,
                    'status' => $item->lastVersion->status->value,
                    'submitted_at' => $item->lastVersion->submitted_at->toIso8601String(),
                ],
            ])->values(),
        ]);
    }

    /**
     * Steps 3-4 and BR-005: the version to download, the committee pronouncements and the version history.
     */
    public function show(Request $request, ProjectVersion $projectVersion): Response
    {
        $version = $this->versionOrFail($projectVersion);

        Gate::authorize('review', $version);

        $version->load('graduationProcess.student.user');
        $currentMembers = $version->graduationProcess
            ->currentCommitteeMembers()
            ->with('teacher')
            ->orderBy('id')
            ->get();
        $approvals = $version->versionApprovals()
            ->whereIn('committee_member_id', $currentMembers->modelKeys())
            ->get()
            ->keyBy('committee_member_id');
        $versionObservations = $version->observations()->with('author')->get();

        // UC-008 step 4: Show who already pronounced, with the date and whether they approved.
        $members = $currentMembers->map(function (CommitteeMember $member) use ($approvals, $versionObservations): array {
            $approval = $approvals->get($member->id);
            $observations = $versionObservations->where('author_id', $member->teacher->user_id);
            $lastObservation = $observations->last();

            return [
                'name' => $member->teacher->user->name,
                'pronounced' => $approval !== null || $observations->isNotEmpty(),
                'approved' => $approval !== null,
                'pronounced_at' => $approval?->approved_at?->toIso8601String()
                    ?? $lastObservation?->recorded_at->toIso8601String(),
            ];
        })->values();

        // UC-008 BR-005: Earlier versions keep their phase and the observations with their author.
        $previousVersions = ProjectVersion::query()
            ->where('graduation_process_id', $version->graduation_process_id)
            ->where('number', '<', $version->number)
            ->with('observations.author')
            ->orderBy('number')
            ->get();

        $currentMember = $currentMembers->firstWhere('teacher_id', $request->user()->teacher?->id);
        $alreadyPronounced = $currentMember !== null
            && ($approvals->has($currentMember->id) || $versionObservations->contains('author_id', $request->user()->id));
        $canPronounce = $currentMember !== null
            && ! $alreadyPronounced
            && $version->status === ProjectVersionStatus::InReview
            && $currentMembers->count() === 3;

        return Inertia::render('committee-reviews/show', [
            'version' => [
                'id' => $version->id,
                'student' => $version->graduationProcess->student->user->name,
                'title' => $version->title,
                'number' => $version->number,
                'phase' => $version->phase->value,
                'status' => $version->status->value,
                'file_name' => $version->original_file_name,
                'file_size_bytes' => $version->file_size_bytes,
                'submitted_at' => $version->submitted_at->toIso8601String(),
            ],
            'members' => $members,
            'observations' => $versionObservations->map(fn (Observation $observation): array => [
                'id' => $observation->id,
                'author' => $observation->author->name,
                'body' => $observation->body,
                'recorded_at' => $observation->recorded_at->toIso8601String(),
            ])->values(),
            'previousVersions' => $previousVersions->map(fn (ProjectVersion $previous): array => [
                'id' => $previous->id,
                'number' => $previous->number,
                'phase' => $previous->phase->value,
                'status' => $previous->status->value,
                'title' => $previous->title,
                'submitted_at' => $previous->submitted_at->toIso8601String(),
                'evaluated_at' => $previous->evaluated_at?->toIso8601String(),
                'observations' => $previous->observations->map(fn (Observation $observation): array => [
                    'id' => $observation->id,
                    'author' => $observation->author->name,
                    'body' => $observation->body,
                    'recorded_at' => $observation->recorded_at->toIso8601String(),
                ])->values(),
            ])->values(),
            'canPronounce' => $canPronounce,
        ]);
    }

    /**
     * Steps 5-11, A1-A3 and A6: register the member's pronouncement and confirm the resulting status.
     */
    public function store(
        RegisterCommitteePronouncementRequest $request,
        ProjectVersion $projectVersion,
        RegisterCommitteePronouncement $registerPronouncement,
    ): RedirectResponse {
        $version = $this->versionOrFail($projectVersion);

        $version = $registerPronouncement->handle(
            $version,
            $request->approves(),
            $request->observationTexts(),
            $request->user(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Pronunciamiento registrado. Estado de la versión: {$version->status->value}.",
        ]);

        return to_route('committee-reviews.index');
    }

    /**
     * Step 4: download the version under review.
     */
    public function download(ProjectVersion $projectVersion): StreamedResponse
    {
        $version = $this->versionOrFail($projectVersion);

        Gate::authorize('review', $version);

        return Storage::disk('local')->download($version->file_path, $version->original_file_name);
    }

    private function versionOrFail(ProjectVersion $projectVersion): ProjectVersion
    {
        // UC-008 BR-001: Only pre-defense versions are reviewed by the committee.
        abort_unless($projectVersion->phase === Phase::PreDefense, 404);

        return $projectVersion;
    }
}
