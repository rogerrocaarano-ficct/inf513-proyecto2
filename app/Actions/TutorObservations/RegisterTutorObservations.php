<?php

namespace App\Actions\TutorObservations;

use App\Actions\Notifications\NotifyUser;
use App\Models\GraduationProcess;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Records the observations of the tutor on a project version and notifies the student (UC-007).
 */
class RegisterTutorObservations
{
    public function __construct(private NotifyUser $notifyUser) {}

    /**
     * @param  list<string>  $observations  The observation texts, already validated and not blank
     * @return list<Observation>
     *
     * @throws ValidationException
     */
    public function handle(ProjectVersion $version, array $observations, User $tutor): array
    {
        return DB::transaction(function () use ($version, $observations, $tutor): array {
            $process = GraduationProcess::query()->whereKey($version->graduation_process_id)->lockForUpdate()->firstOrFail();

            // UC-007 BR-001: Re-check under the lock that the tutor is current and the process in progress (A4).
            $permission = Gate::forUser($tutor)->inspect('viewAsTutor', $process);

            if ($permission->denied()) {
                throw ValidationException::withMessages(['project_version' => (string) $permission->message()]);
            }

            $recordedAt = now();

            // UC-007 BR-003: The observations are guidance; the status of the version does not change.
            // UC-007 BR-004: A recorded observation is only added, never changed or deleted.
            $recorded = array_map(fn (string $text): Observation => Observation::query()->create([
                'author_id' => $tutor->id,
                'project_version_id' => $version->id,
                'body' => $text,
                'recorded_at' => $recordedAt,
            ]), $observations);

            $this->notifyUser->handle(
                $process->student()->firstOrFail()->user,
                "Su versión {$version->number} del proyecto recibió observaciones de su tutor.",
                $process,
            );

            return $recorded;
        });
    }
}
