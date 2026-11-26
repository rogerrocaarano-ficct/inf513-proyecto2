<?php

namespace App\Actions\ProjectVersions;

use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Registers a new project version of the student's process in its current phase and notifies who reviews it (UC-004).
 */
class SubmitProjectVersion
{
    public function __construct(
        private FindVersionAdmission $findAdmission,
        private NotifyUser $notifyUser,
    ) {}

    /**
     * @param  string|null  $title  The title the student enters; only a project profile takes it.
     *
     * @throws ValidationException
     */
    public function handle(User $student, ?string $title, UploadedFile $file): SubmittedProjectVersion
    {
        $storedPath = null;

        try {
            return DB::transaction(function () use ($student, $title, $file, &$storedPath): SubmittedProjectVersion {
                $process = $this->lockedProcess($student);

                $submittedAt = now();
                $phase = $process->phase;

                // UC-004 BR-003: The next number is the highest of the process plus one, across phases.
                $number = (int) ProjectVersion::query()->where('graduation_process_id', $process->id)->max('number') + 1;

                // UC-004 BR-005: Without a designated workshop teacher the profile is in review without reviewer.
                $reviewer = $phase === Phase::ProfileEvaluation ? Teacher::query()->workshopTeacher()->with('user')->first() : null;

                // UC-004 BR-001: The version is a PDF kept on the file storage of the server (C-014).
                $storedPath = (string) $file->store("project-versions/{$process->id}", 'local');

                $version = ProjectVersion::query()->create([
                    'graduation_process_id' => $process->id,
                    'number' => $number,
                    'phase' => $phase,
                    'title' => $this->titleFor($process, $title),
                    'file_path' => $storedPath,
                    'original_file_name' => $file->getClientOriginalName(),
                    'file_size_bytes' => $file->getSize(),
                    // UC-004 BR-004: A formal defense version is not reviewed, so it is presented; the others wait in review.
                    'status' => $phase === Phase::FormalDefense ? ProjectVersionStatus::Submitted : ProjectVersionStatus::InReview,
                    'reviewer_id' => $reviewer?->id,
                    'submitted_at' => $submittedAt,
                ]);

                $this->notify($process, $version, $reviewer);

                return new SubmittedProjectVersion($version, $phase === Phase::ProfileEvaluation && $reviewer === null);
            });
        } catch (Throwable $exception) {
            // UC-004 Failure postcondition: no file remains stored for a version that was not registered.
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }
    }

    /**
     * The student's process, locked, after checking again that its phase admits a new version.
     *
     * @throws ValidationException
     */
    private function lockedProcess(User $student): GraduationProcess
    {
        $admission = $this->findAdmission->handle($student);

        if ($admission->process !== null) {
            GraduationProcess::query()->whereKey($admission->process->id)->lockForUpdate()->firstOrFail();
            $admission = $this->findAdmission->handle($student);
        }

        // UC-004 BR-002: Re-check under the lock that the phase still admits a new version.
        if (! $admission->isAdmitted()) {
            throw ValidationException::withMessages(['project_version' => (string) $admission->reason]);
        }

        return $admission->process;
    }

    private function titleFor(GraduationProcess $process, ?string $title): string
    {
        // UC-004 BR-007: The profile takes the title entered; later phases carry the fixed project title.
        if ($process->phase === Phase::ProfileEvaluation) {
            return (string) $title;
        }

        return $process->project_title
            ?? (string) $process->projectVersions()->profiles()->where('status', ProjectVersionStatus::Approved)->value('title');
    }

    private function notify(GraduationProcess $process, ProjectVersion $version, ?Teacher $reviewer): void
    {
        $student = $process->student->user->name;

        // UC-004 BR-004: Who receives the version depends on the phase.
        if ($process->phase === Phase::ProfileEvaluation) {
            if ($reviewer !== null) {
                $this->notifyUser->handle(
                    $reviewer->user,
                    "{$student} presentó el perfil de proyecto (versión {$version->number}), que espera su revisión.",
                    $process,
                );
            }

            return;
        }

        $message = $process->phase === Phase::PreDefense
            ? "{$student} presentó la versión {$version->number} del proyecto de grado, que espera su revisión."
            : "{$student} presentó la versión {$version->number} del proyecto de grado para la defensa formal.";

        $recipients = $process->currentCommitteeMembers()->with('teacher.user')->get()
            ->map(fn (CommitteeMember $member): User => $member->teacher->user);

        if ($process->tutor_id !== null) {
            $recipients->prepend($process->tutor()->with('user')->firstOrFail()->user);
        }

        foreach ($recipients->unique('id') as $recipient) {
            $this->notifyUser->handle($recipient, $message, $process);
        }
    }
}
