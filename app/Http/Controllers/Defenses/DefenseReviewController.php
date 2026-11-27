<?php

namespace App\Http\Controllers\Defenses;

use App\Actions\Defenses\FindCurrentCommitteeMember;
use App\Actions\Defenses\FindPreDefenseDetail;
use App\Actions\Defenses\ListPendingPreDefenses;
use App\Actions\Defenses\PreDefenseDetail;
use App\Actions\Defenses\RegisterPreDefense;
use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Enums\PreDefenseDecision;
use App\Enums\ProjectVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Defenses\RegisterPreDefenseRequest;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DefenseReviewController extends Controller
{
    /**
     * Steps 1-2 and A4: the pre-defenses this teacher still has to register.
     */
    public function index(Request $request, ListPendingPreDefenses $listPending): Response
    {
        /** @var User $user */
        $user = $request->user();
        $teacher = $user->teacher;
        $acts = $teacher === null ? collect() : $listPending->handle($teacher);

        return Inertia::render('defense-reviews/index', [
            'preDefenses' => $acts->map(fn (Defense $act): array => [
                'id' => $act->id,
                'student' => $act->graduationProcess->student->user->name,
                'scheduled_at' => $act->scheduled_at->toIso8601String(),
                'location' => $act->location,
                'schedule_revision' => $act->schedule_revision,
                'reported_by' => $act->reviews->count(),
            ])->values(),
            'message' => $acts->isEmpty() ? 'No hay pre-defensas pendientes de su registro.' : null,
        ]);
    }

    /**
     * Step 4: the approved version to download and the members who already registered.
     */
    public function show(
        Request $request,
        Defense $defense,
        FindCurrentCommitteeMember $findMember,
        FindPreDefenseDetail $findDetail,
    ): Response {
        Gate::authorize('registerPreDefense', $defense);

        $member = $findMember->forDefense($request->user(), $defense);

        if ($member === null) {
            abort(403);
        }

        $detail = $findDetail->handle($defense, $member);

        return Inertia::render('defense-reviews/show', [
            'preDefense' => $this->describePreDefense($detail),
            'reportedBy' => $detail->reportedBy->map(fn (CommitteeMember $reporter): array => [
                'id' => $reporter->id,
                'name' => $reporter->teacher->user->name,
                'recorded_at' => Carbon::parse($reporter->pivot->recorded_at)->toIso8601String(),
            ])->values(),
            // UC-010 step 5 and BR-004: the absence is offered only while nobody registered the pre-defense.
            'decisions' => collect(PreDefenseDecision::cases())
                ->reject(fn (PreDefenseDecision $decision): bool => $decision === PreDefenseDecision::Absence
                    && $detail->reportedBy->isNotEmpty())
                ->map(fn (PreDefenseDecision $decision): array => [
                    'value' => $decision->value,
                    'label' => match ($decision) {
                        PreDefenseDecision::Observations => 'Registrar observaciones',
                        PreDefenseDecision::NoObservations => 'No hubo observaciones',
                        PreDefenseDecision::Absence => 'El estudiante no se presentó',
                    },
                ])->values(),
        ]);
    }

    /**
     * Steps 5-11 and A1-A5: register the decision of the current member.
     */
    public function store(
        RegisterPreDefenseRequest $request,
        Defense $defense,
        FindCurrentCommitteeMember $findMember,
        RegisterPreDefense $registerPreDefense,
    ): RedirectResponse {
        $member = $findMember->forDefense($request->user(), $defense);

        if ($member === null) {
            abort(403);
        }

        $decision = $request->decision();

        try {
            $registerPreDefense->handle(
                $member,
                $defense,
                $decision,
                $request->observationTexts(),
                $request->integer('schedule_revision'),
            );
        } catch (ValidationException $exception) {
            $message = $exception->errors()['availability'][0] ?? null;

            if ($message === null) {
                throw $exception;
            }

            // UC-010 A5: report why it is no longer available and refresh the pending list.
            Inertia::flash('toast', ['type' => 'error', 'message' => $message]);

            return to_route('defense-reviews.index');
        }

        // UC-010 step 11 and A1: the confirmation includes the resulting state of the pre-defense.
        $act = $defense->fresh();
        $message = match (true) {
            $decision === PreDefenseDecision::Absence => 'Se registró la inasistencia del estudiante. El trámite de titulación quedó abandonado.',
            $act?->is_held === true => 'Se registró la pre-defensa. La pre-defensa quedó realizada y el trámite pasó a la fase defensa formal.',
            default => 'Se registró la pre-defensa. Faltan registros de otros miembros del tribunal; la pre-defensa sigue pendiente.',
        };

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('defense-reviews.index');
    }

    /**
     * Step 4, NFR-004 and NFR-008: download the approved pre-defense version.
     */
    public function download(
        Defense $defense,
        ProjectVersion $projectVersion,
    ): StreamedResponse {
        Gate::authorize('downloadVersion', $defense);

        // UC-010 step 4: only the approved pre-defense version of this act is offered for download.
        abort_unless(
            $projectVersion->graduation_process_id === $defense->graduation_process_id
                && $projectVersion->phase === Phase::PreDefense
                && $projectVersion->status === ProjectVersionStatus::Approved,
            404,
        );

        // NFR-008: files are kept for download while the graduation process exists.
        return Storage::disk('local')->download($projectVersion->file_path, $projectVersion->original_file_name);
    }

    /** @return array<string, mixed> */
    private function describePreDefense(PreDefenseDetail $detail): array
    {
        $act = $detail->defense;
        $version = $detail->version;

        return [
            'id' => $act->id,
            'student' => $act->graduationProcess->student->user->name,
            'scheduled_at' => $act->scheduled_at->toIso8601String(),
            'schedule_revision' => $act->schedule_revision,
            'location' => $act->location,
            'type' => DefenseType::PreDefense->value,
            'version' => $version === null ? null : [
                'id' => $version->id,
                'number' => $version->number,
                'title' => $version->title,
                'phase' => $version->phase->value,
                'status' => $version->status->value,
                'file_name' => $version->original_file_name,
                'file_size_bytes' => $version->file_size_bytes,
                'submitted_at' => $version->submitted_at->toIso8601String(),
                'download_url' => route('defense-reviews.download', [$act, $version]),
            ],
        ];
    }
}
