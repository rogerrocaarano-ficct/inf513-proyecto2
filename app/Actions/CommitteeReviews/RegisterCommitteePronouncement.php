<?php

namespace App\Actions\CommitteeReviews;

use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\User;
use App\Models\VersionApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Records the pronouncement of a committee member, decides the version status and notifies the student (UC-008).
 */
class RegisterCommitteePronouncement
{
    public const string OBSERVATION_REQUIRED = 'El pronunciamiento necesita al menos una observación.';

    public const string OBSERVATION_TOO_LONG = 'Cada observación admite como máximo 2000 caracteres.';

    public const string CANNOT_REGISTER = 'El pronunciamiento ya no puede registrarse.';

    private const string APPROVED_MESSAGE = 'El tribunal evaluador aprobó su versión %d del proyecto de grado y espera la programación de su pre-defensa.';

    private const string OBSERVED_MESSAGE = 'Su versión %d del proyecto de grado recibió observaciones del tribunal evaluador. Puede presentar una nueva versión con ellas subsanadas.';

    public function __construct(private NotifyUser $notifyUser) {}

    /**
     * @param  list<string|null>  $observations  The observation texts; blank ones are discarded
     *
     * @throws ValidationException
     */
    public function handle(
        ProjectVersion $version,
        bool $approves,
        array $observations,
        User $member,
    ): ProjectVersion {
        $texts = array_values(array_filter(
            array_map(fn (?string $text): string => trim((string) $text), $observations),
            fn (string $text): bool => $text !== '',
        ));

        // UC-008 A1, BR-002: Observing without approving needs at least one observation.
        if (! $approves && $texts === []) {
            throw ValidationException::withMessages(['observations' => self::OBSERVATION_REQUIRED]);
        }

        // UC-008 BR-006: An observation has at most 2000 characters.
        foreach ($texts as $text) {
            if (mb_strlen($text) > 2000) {
                throw ValidationException::withMessages(['observations' => self::OBSERVATION_TOO_LONG]);
            }
        }

        return DB::transaction(function () use ($version, $approves, $texts, $member): ProjectVersion {
            $version = ProjectVersion::query()->whereKey($version->getKey())->lockForUpdate()->firstOrFail();
            $process = GraduationProcess::query()->whereKey($version->graduation_process_id)->lockForUpdate()->firstOrFail();

            $teacherId = $member->teacher?->id;
            $committeeMember = $teacherId === null ? null : CommitteeMember::query()
                ->where('graduation_process_id', $process->id)
                ->where('teacher_id', $teacherId)
                ->current()
                ->lockForUpdate()
                ->first();

            // UC-008 A6: A replaced member, a closed process or a version outside review cannot be pronounced.
            if ($committeeMember === null
                || $process->concluded_at !== null
                || $process->abandoned_at !== null
                || $version->phase !== Phase::PreDefense
                || $version->status !== ProjectVersionStatus::InReview) {
                throw ValidationException::withMessages(['project_version' => self::CANNOT_REGISTER]);
            }

            // UC-008 BR-002, BR-004: A current member pronounces once; a replaced member's pronouncement is ignored.
            if ($version->versionApprovals()->where('committee_member_id', $committeeMember->id)->exists()
                || $version->observations()->where('author_id', $member->id)->exists()) {
                throw ValidationException::withMessages(['project_version' => self::CANNOT_REGISTER]);
            }

            $evaluatedAt = now();

            if ($approves) {
                VersionApproval::query()->create([
                    'project_version_id' => $version->id,
                    'committee_member_id' => $committeeMember->id,
                    'approved_at' => $evaluatedAt,
                ]);
            }

            foreach ($texts as $text) {
                Observation::query()->create([
                    'author_id' => $member->id,
                    'project_version_id' => $version->id,
                    'body' => $text,
                    'recorded_at' => $evaluatedAt,
                ]);
            }

            $currentMembers = $process->currentCommitteeMembers()->with('teacher')->orderBy('id')->get();
            $approvals = VersionApproval::query()
                ->where('project_version_id', $version->id)
                ->whereIn('committee_member_id', $currentMembers->modelKeys())
                ->get();
            $observedUserIds = $version->observations()->pluck('author_id');

            // UC-008 BR-004: Only the three current members count, so a replacement restarts the count.
            $allApproved = $currentMembers->count() === 3 && $approvals->count() === 3;
            $allPronounced = $currentMembers->count() === 3 && $currentMembers->every(
                fn (CommitteeMember $current): bool => $approvals->contains('committee_member_id', $current->id)
                    // UC-008 BR-003: Only observations of a member who did not approve block the version;
                    // tutor observations and suggestions of approving members do not.
                    || $observedUserIds->contains($current->teacher->user_id),
            );

            if ($allApproved || $allPronounced) {
                $status = $allApproved ? ProjectVersionStatus::Approved : ProjectVersionStatus::WithObservations;
                $version->forceFill(['status' => $status, 'evaluated_at' => $evaluatedAt])->save();

                // C-005: The student is notified only inside the system, never by email or another channel.
                $process->loadMissing('student.user');
                $this->notifyUser->handle(
                    $process->student->user,
                    sprintf($allApproved ? self::APPROVED_MESSAGE : self::OBSERVED_MESSAGE, $version->number),
                    $process,
                );
            }

            return $version;
        });
    }
}
