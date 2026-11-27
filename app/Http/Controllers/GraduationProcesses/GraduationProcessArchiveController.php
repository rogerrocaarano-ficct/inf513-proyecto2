<?php

namespace App\Http\Controllers\GraduationProcesses;

use App\Actions\GraduationProcesses\ArchiveGraduationProcess;
use App\Enums\DefenseType;
use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\ProjectVersion;
use App\Models\RequirementDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GraduationProcessArchiveController extends Controller
{
    public function index(): Response
    {
        $now = now()->startOfSecond();
        $processes = GraduationProcess::query()->whereNull('archived_at')
            ->where(fn (Builder $query) => $query->where('concluded_at', '<', $now)->orWhere('abandoned_at', '<', $now))
            ->with(['student.user', 'defenses' => fn ($query) => $query->where('is_held', true)])
            ->orderByRaw('COALESCE(concluded_at, abandoned_at)')
            ->orderBy('id')
            ->get();

        return Inertia::render('graduation-archives/index', [
            'processes' => $processes->map(fn (GraduationProcess $process): array => $this->summary($process)),
        ]);
    }

    public function show(GraduationProcess $graduationProcess): Response
    {
        $this->ensureEnded($graduationProcess);

        $graduationProcess->load([
            'student.user', 'tutor.user', 'currentCommitteeMembers.teacher.user',
            'requirementDocuments', 'projectVersions',
            'defenses' => fn ($query) => $query->where('is_held', true),
        ]);

        $absence = $graduationProcess->defenses->firstWhere('is_student_absent', true);

        return Inertia::render('graduation-archives/show', [
            'process' => [
                ...$this->summary($graduationProcess),
                'archived_at' => $graduationProcess->archived_at?->toIso8601String(),
                'tutor' => $graduationProcess->tutor?->user->name,
                'committee' => $graduationProcess->currentCommitteeMembers->map(
                    fn (CommitteeMember $member): string => $member->teacher->user->name,
                )->values(),
                'grade' => $this->grade($graduationProcess),
                'absence' => $absence === null ? null : [
                    'act' => $absence->type->value,
                    'scheduled_at' => $absence->scheduled_at->toIso8601String(),
                ],
            ],
            'documents' => $graduationProcess->requirementDocuments->map(fn (RequirementDocument $document): array => [
                'id' => $document->id,
                'type' => $document->type->value,
                'name' => $document->original_file_name,
                'url' => route('graduation-archives.documents', [$graduationProcess, $document]),
            ])->values(),
            'versions' => $graduationProcess->projectVersions->map(fn (ProjectVersion $version): array => [
                'id' => $version->id,
                'number' => $version->number,
                'phase' => $version->phase->value,
                'name' => $version->original_file_name,
                'url' => route('graduation-archives.versions', [$graduationProcess, $version]),
            ])->values(),
        ]);
    }

    public function store(Request $request, GraduationProcess $graduationProcess, ArchiveGraduationProcess $archive): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $archive->handle($graduationProcess, $user);
        } catch (ValidationException $exception) {
            $message = $exception->errors()['availability'][0] ?? null;

            if ($message === null) {
                throw $exception;
            }

            Inertia::flash('toast', ['type' => 'error', 'message' => $message]);

            return to_route('graduation-archives.index');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'El trámite de titulación fue archivado.']);

        return to_route('graduation-archives.index');
    }

    public function document(GraduationProcess $graduationProcess, RequirementDocument $requirementDocument): StreamedResponse
    {
        $this->ensureEnded($graduationProcess);
        abort_unless($requirementDocument->graduation_process_id === $graduationProcess->id, 404);

        return Storage::disk('local')->download($requirementDocument->file_path, $requirementDocument->original_file_name);
    }

    public function version(GraduationProcess $graduationProcess, ProjectVersion $projectVersion): StreamedResponse
    {
        $this->ensureEnded($graduationProcess);
        abort_unless($projectVersion->graduation_process_id === $graduationProcess->id, 404);

        return Storage::disk('local')->download($projectVersion->file_path, $projectVersion->original_file_name);
    }

    private function ensureEnded(GraduationProcess $process): void
    {
        abort_if($process->concluded_at === null && $process->abandoned_at === null, 404);
    }

    /** @return array{id: int, student: string, title: string|null, result: string, ended_at: string|null} */
    private function summary(GraduationProcess $process): array
    {
        $grade = $this->grade($process);

        return [
            'id' => $process->id,
            'student' => $process->student->user->name,
            'title' => $process->project_title,
            'result' => match (true) {
                $process->abandoned_at !== null => 'Abandonado',
                $grade === null => 'Concluido',
                $grade >= GraduationProcess::PASSING_GRADE => 'Aprobado',
                default => 'Reprobado',
            },
            'ended_at' => ($process->concluded_at ?? $process->abandoned_at)?->toIso8601String(),
        ];
    }

    private function grade(GraduationProcess $process): ?int
    {
        $defense = $process->defenses->first(
            fn (Defense $act): bool => $act->type === DefenseType::FormalDefense && $act->grade !== null,
        );

        return $defense?->grade;
    }
}
