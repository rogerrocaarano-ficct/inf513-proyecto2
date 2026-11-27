<?php

namespace App\Actions\Defenses;

use App\Actions\GraduationProcesses\ChangePhase;
use App\Actions\Notifications\NotifyUser;
use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Enums\PreDefenseDecision;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Observation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Records a committee member's decision on a pre-defense and notifies the participants (UC-010).
 */
class RegisterPreDefense
{
    public function __construct(
        private ChangePhase $changePhase,
        private NotifyUser $notifyUser,
    ) {}

    /**
     * @param  list<string>  $observations
     *
     * @throws ValidationException
     */
    public function handle(
        CommitteeMember $member,
        Defense $defense,
        ?PreDefenseDecision $decision,
        array $observations,
        int $scheduleRevision,
    ): void {
        DB::transaction(function () use ($member, $defense, $decision, $observations, $scheduleRevision): void {
            // UC-010 A3: an invalid decision or observation leaves no writes behind.
            $texts = $this->validatedObservations($decision, $observations);

            // UC-010 BR-001: serialize the registrations of the same act (as UC-009 does for the calendar).
            DB::select('select pg_advisory_xact_lock(513, 10)');

            $process = GraduationProcess::query()
                ->whereKey($defense->graduation_process_id)
                ->lockForUpdate()
                ->first();

            if ($process === null || $process->concluded_at !== null
                || $process->abandoned_at !== null || $process->phase !== Phase::PreDefense) {
                throw $this->unavailable('El trámite de titulación ya no está en curso en la fase pre-defensa.');
            }

            $act = Defense::query()->whereKey($defense->id)->lockForUpdate()->first();

            if ($act === null || $act->type !== DefenseType::PreDefense) {
                throw $this->unavailable('El acto seleccionado ya no está disponible.');
            }

            if ($act->is_held) {
                throw $this->unavailable('La pre-defensa ya se realizó.');
            }

            // UC-010 step 6: keep the schedule the member consulted, as UC-009 does when rescheduling.
            if ($act->schedule_revision !== $scheduleRevision) {
                throw $this->unavailable('La programación cambió desde que abrió el formulario. Consulte las pre-defensas pendientes.');
            }

            // UC-010 BR-002: the act is registered only after its scheduled start.
            if ($act->scheduled_at->isFuture()) {
                throw $this->unavailable('La pre-defensa aún no llegó a su hora de inicio.');
            }

            // UC-010 BR-001: only a current member of the tribunal registers, once, and the registration is immutable.
            $currentMember = $process->currentCommitteeMembers()->lockForUpdate()->find($member->id);

            if ($currentMember === null) {
                throw $this->unavailable('Ya no es miembro vigente del tribunal de este trámite.');
            }

            if ($act->reviews()->where('committee_member_id', $currentMember->id)->exists()) {
                throw $this->unavailable('Usted ya registró esta pre-defensa.');
            }

            $recordedAt = now();
            $student = $process->student()->firstOrFail()->user;
            $tutor = $process->tutor?->user;
            $recordingUserId = $currentMember->teacher->user_id;

            if ($decision === PreDefenseDecision::Absence) {
                $this->registerAbsence($process, $act, $currentMember, $student, $tutor, $recordingUserId, $recordedAt);

                return;
            }

            // UC-010 BR-003: each current member records observations or that there were none; a record is not changed.
            $act->reviews()->create([
                'committee_member_id' => $currentMember->id,
                'has_observations' => $decision === PreDefenseDecision::Observations,
                'grade' => null,
                'recorded_at' => $recordedAt,
            ]);

            // UC-010 BR-005: observations belong to the pre-defense, are never edited or deleted and hold at most
            // 2000 characters; the author is the user of the committee member, not the committee member row.
            foreach ($texts as $text) {
                Observation::query()->create([
                    'author_id' => $recordingUserId,
                    'defense_id' => $act->id,
                    'body' => $text,
                    'recorded_at' => $recordedAt,
                ]);
            }

            $currentCount = $process->currentCommitteeMembers()->count();
            $registeredCount = $act->committeeMembers()
                ->whereNull('committee_members.replaced_at')
                ->count();

            // UC-010 A1: with missing registrations the act stays pending and the phase does not change.
            if ($registeredCount !== 3 || $currentCount !== 3) {
                return;
            }

            // UC-010 BR-003: the three current registrations hold the act and move the process to the formal defense;
            // the observations on the defense let the student submit versions there (UC-004 BR-006).
            $act->forceFill(['is_held' => true])->save();
            $this->changePhase->handle($process, Phase::FormalDefense, $currentMember->teacher->user, $recordedAt);

            $this->notifyRegistration($act, $student, $tutor);
        });
    }

    /**
     * @param  list<string>  $observations
     * @return list<string>
     */
    private function validatedObservations(?PreDefenseDecision $decision, array $observations): array
    {
        if ($decision === null) {
            throw ValidationException::withMessages([
                'decision' => 'Elija si registra observaciones, que no las hubo o la inasistencia del estudiante.',
            ]);
        }

        if ($decision !== PreDefenseDecision::Observations) {
            return [];
        }

        $texts = array_values(array_filter(
            array_map('trim', $observations),
            fn (string $text): bool => $text !== '',
        ));

        // UC-010 A3 and BR-005: observations cannot be blank.
        if ($texts === []) {
            throw ValidationException::withMessages([
                'observations' => 'Registre al menos una observación.',
            ]);
        }

        foreach ($texts as $text) {
            if (mb_strlen($text) > 2000) {
                throw ValidationException::withMessages([
                    'observations' => 'Cada observación admite como máximo 2000 caracteres.',
                ]);
            }
        }

        return $texts;
    }

    private function registerAbsence(
        GraduationProcess $process,
        Defense $act,
        CommitteeMember $member,
        User $student,
        ?User $tutor,
        int $recordingUserId,
        CarbonInterface $recordedAt,
    ): void {
        // UC-010 BR-004: one current member records the absence while nobody registered the pre-defense.
        if ($act->reviews()->exists()) {
            throw $this->unavailable('Otro miembro ya registró la pre-defensa; ya no se puede informar la inasistencia.');
        }

        $act->forceFill([
            'is_held' => true,
            'is_student_absent' => true,
            'absence_recorded_by_id' => $member->id,
        ])->save();

        $process->forceFill(['abandoned_at' => $recordedAt])->save();

        // UC-010 A2 (OQ-027, OQ-028 and OQ-058): the absence abandons the process and reaches the student, the tutor and
        // the other current members; an absent act has no DEFENSE_REVIEW and no observations (entity model).
        $others = $process->currentCommitteeMembers()
            ->with('teacher.user')
            ->whereKeyNot($member->id)
            ->get()
            ->map(fn (CommitteeMember $other) => $other->teacher->user);

        $recipients = collect([$student, $tutor])
            ->merge($others)
            ->filter()
            ->unique('id')
            ->reject(fn ($recipient): bool => $recipient->id === $recordingUserId);

        // C-005: notifications stay inside the system.
        foreach ($recipients as $recipient) {
            $this->notifyUser->handle(
                $recipient,
                "Se registró la inasistencia de {$student->name} a la pre-defensa. El trámite de titulación quedó abandonado.",
                $process,
            );
        }
    }

    private function notifyRegistration(Defense $act, User $student, ?User $tutor): void
    {
        // UC-010 step 10 (OQ-028 and OQ-058): the student and the tutor receive the observations, or that there were none,
        // and the change of phase, with the same internal-notification style as UC-009 (C-005).
        $observations = $act->observations()->with('author')->get();
        $details = $observations->isEmpty()
            ? 'No hubo observaciones'
            : $observations->pluck('body')->join('; ');

        $message = Str::limit(
            "Se registró la pre-defensa de {$student->name}. Observaciones del tribunal: {$details}. "
            .'El trámite pasó a la fase defensa formal.',
            500,
        );

        foreach (collect([$student, $tutor])->filter()->unique('id') as $recipient) {
            $this->notifyUser->handle($recipient, $message, $act->graduationProcess);
        }
    }

    private function unavailable(string $message): ValidationException
    {
        // UC-010 A5: an unavailable registration is reported in Spanish without writing anything.
        return ValidationException::withMessages(['availability' => $message]);
    }
}
