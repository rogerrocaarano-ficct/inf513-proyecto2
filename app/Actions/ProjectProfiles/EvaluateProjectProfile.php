<?php

namespace App\Actions\ProjectProfiles;

use App\Actions\Notifications\NotifyUser;
use App\Enums\ProjectVersionStatus;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Approves or rejects a project profile with its observations and notifies the student (UC-005).
 */
class EvaluateProjectProfile
{
    public const string REJECTION_NEEDS_OBSERVATION = 'El rechazo necesita al menos una observación.';

    public function __construct(private NotifyUser $notifyUser) {}

    /**
     * @param  array<int, string|null>  $observations  The observation texts; blank ones are discarded
     *
     * @throws ValidationException
     */
    public function handle(
        ProjectVersion $profile,
        ProjectVersionStatus $result,
        array $observations,
        User $reviewer,
    ): ProjectVersion {
        $texts = array_values(array_filter(
            array_map(fn (?string $text): string => trim((string) $text), $observations),
            fn (string $text): bool => $text !== '',
        ));

        // UC-005 BR-002: A profile is approved or rejected; a rejection has at least one observation.
        if (! in_array($result, [ProjectVersionStatus::Approved, ProjectVersionStatus::Rejected], true)) {
            throw ValidationException::withMessages(['decision' => 'La evaluación aprueba o rechaza el perfil de proyecto.']);
        }

        if ($result === ProjectVersionStatus::Rejected && $texts === []) {
            throw ValidationException::withMessages(['observations' => self::REJECTION_NEEDS_OBSERVATION]);
        }

        return DB::transaction(function () use ($profile, $result, $texts, $reviewer): ProjectVersion {
            $profile = ProjectVersion::query()->whereKey($profile->getKey())->lockForUpdate()->firstOrFail();

            // UC-005 BR-003: An evaluated profile never changes its result.
            if ($profile->status !== ProjectVersionStatus::InReview) {
                throw ValidationException::withMessages([
                    'project_version' => 'El perfil de proyecto ya fue evaluado.',
                ]);
            }

            // UC-005 BR-004: A process has at most one approved project profile.
            if ($result === ProjectVersionStatus::Approved
                && ProjectVersion::query()
                    ->profiles()
                    ->where('graduation_process_id', $profile->graduation_process_id)
                    ->where('status', ProjectVersionStatus::Approved)
                    ->exists()) {
                throw ValidationException::withMessages([
                    'project_version' => 'El trámite de titulación ya tiene un perfil de proyecto aprobado.',
                ]);
            }

            $evaluatedAt = now();

            foreach ($texts as $text) {
                Observation::query()->create([
                    'author_id' => $reviewer->id,
                    'project_version_id' => $profile->id,
                    'body' => $text,
                    'recorded_at' => $evaluatedAt,
                ]);
            }

            $profile->forceFill(['status' => $result, 'evaluated_at' => $evaluatedAt])->save();

            $process = $profile->graduationProcess()->with('student.user')->firstOrFail();

            if ($result === ProjectVersionStatus::Approved) {
                // Step 7: the approved title becomes the project title; the phase stays profile evaluation.
                $process->forceFill(['project_title' => $profile->title])->save();
            }

            $this->notifyUser->handle(
                $process->student->user,
                $result === ProjectVersionStatus::Approved
                    ? "Su perfil de proyecto (versión {$profile->number}) fue aprobado."
                    : "Su perfil de proyecto (versión {$profile->number}) fue rechazado. Puede presentar uno nuevo con las observaciones subsanadas.",
                $process,
            );

            return $profile;
        });
    }
}
