<?php

namespace App\Http\Controllers\Defenses;

use App\Actions\Defenses\FindCurrentCommitteeMember;
use App\Actions\Defenses\FindFormalDefenseVersion;
use App\Actions\Defenses\ListPendingFormalDefenses;
use App\Actions\Defenses\RegisterFormalDefense;
use App\Enums\DefenseType;
use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormalDefenseReviewController extends Controller
{
    public function index(Request $request, ListPendingFormalDefenses $pending): Response
    {
        /** @var User $user */
        $user = $request->user();
        $teacher = $user->teacher;
        $acts = $teacher === null ? collect() : $pending->query($teacher)
            ->with('graduationProcess.student.user')->orderBy('scheduled_at')->orderBy('id')->get();

        return Inertia::render('formal-defense-reviews/index', [
            'defenses' => $acts->map(fn (Defense $act): array => $this->describe($act))->values(),
        ]);
    }

    public function show(
        Request $request,
        Defense $defense,
        ListPendingFormalDefenses $pending,
        FindFormalDefenseVersion $findVersion,
    ): Response|RedirectResponse {
        Gate::authorize('gradeFormalDefense', $defense);

        /** @var User $user */
        $user = $request->user();
        $teacher = $user->teacher;

        if ($teacher === null || ! $pending->query($teacher)->whereKey($defense->id)->exists()) {
            return $this->unavailable();
        }

        $version = $findVersion->handle($defense);
        $gradedIds = $defense->reviews()->whereNotNull('grade')->pluck('committee_member_id');

        return Inertia::render('formal-defense-reviews/show', [
            'defense' => $this->describe($defense),
            'members' => $defense->graduationProcess->currentCommitteeMembers()->with('teacher.user')
                ->orderBy('id')->get()->map(fn (CommitteeMember $member): array => [
                    'id' => $member->id,
                    'name' => $member->teacher->user->name,
                    'graded' => $gradedIds->contains($member->id),
                ])->values(),
            'canReportAbsence' => ! $defense->reviews()->exists(),
            'version' => $version === null ? null : [
                'number' => $version->number,
                'name' => $version->original_file_name,
                'download_url' => route('formal-defense-reviews.download', [$defense, $version]),
            ],
        ]);
    }

    public function store(
        Request $request,
        Defense $defense,
        FindCurrentCommitteeMember $findMember,
        RegisterFormalDefense $register,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->teacher !== null && $defense->type === DefenseType::FormalDefense, 403);

        if ($findMember->forDefense($user, $defense) === null) {
            abort_unless($defense->graduationProcess->committeeMembers()
                ->where('teacher_id', $user->teacher->id)->exists(), 403);

            return $this->unavailable();
        }

        $request->validate([
            'schedule_revision' => ['required', 'integer', 'min:1'],
            'absent' => ['required', 'boolean'],
        ], [
            'schedule_revision.*' => 'La programación consultada no es válida.',
            'absent.*' => 'Seleccione si va a calificar o registrar la inasistencia.',
        ]);

        try {
            $message = $register->handle(
                $user,
                $defense,
                $request->integer('schedule_revision'),
                $request->boolean('absent'),
                $request->input('grade'),
            );
        } catch (ValidationException $exception) {
            if (isset($exception->errors()['availability'])) {
                return $this->unavailable();
            }

            throw $exception;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('formal-defense-reviews.index');
    }

    public function download(
        Defense $defense,
        ProjectVersion $projectVersion,
        FindFormalDefenseVersion $findVersion,
    ): StreamedResponse {
        Gate::authorize('gradeFormalDefense', $defense);

        abort_unless($defense->scheduled_at->isPast() && $findVersion->handle($defense)?->is($projectVersion), 404);

        return Storage::disk('local')->download($projectVersion->file_path, $projectVersion->original_file_name);
    }

    private function unavailable(): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => RegisterFormalDefense::UNAVAILABLE]);

        return to_route('formal-defense-reviews.index');
    }

    /** @return array{id: int, student: string, scheduled_at: string, location: string|null, schedule_revision: int} */
    private function describe(Defense $defense): array
    {
        return [
            'id' => $defense->id,
            'student' => $defense->graduationProcess->student->user->name,
            'scheduled_at' => $defense->scheduled_at->toIso8601String(),
            'location' => $defense->location,
            'schedule_revision' => $defense->schedule_revision,
        ];
    }
}
