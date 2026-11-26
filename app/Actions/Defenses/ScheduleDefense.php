<?php

namespace App\Actions\Defenses;

use App\Actions\Notifications\NotifyUser;
use App\Enums\DefenseType;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleDefense
{
    public function __construct(
        private DefenseEligibility $eligibility,
        private FindScheduleConflicts $conflicts,
        private NotifyUser $notifyUser,
    ) {}

    public function handle(
        int $processId,
        DefenseType $type,
        Carbon $start,
        ?string $location,
        ?int $defenseId = null,
        ?int $revision = null,
    ): void {
        DB::transaction(function () use ($processId, $type, $start, $location, $defenseId, $revision): void {
            $this->conflicts->lockCalendar();
            $process = GraduationProcess::query()->lockForUpdate()->find($processId);
            if ($process === null || $process->concluded_at !== null || $process->abandoned_at !== null) {
                throw ValidationException::withMessages([
                    'availability' => 'El trámite de titulación ya no está en curso.',
                ]);
            }

            $defense = $this->findAct($process, $type, $defenseId, $revision);

            if (! $start->isFuture()) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'La fecha y hora deben ser futuras.',
                ]);
            }

            $process->load(['student.user', 'tutor.user', 'currentCommitteeMembers.teacher.user']);
            $this->ensureAvailable($process, $start, $location, $defenseId);

            $defense->fill(['scheduled_at' => $start, 'location' => $location]);
            if ($defense->exists) {
                $defense->schedule_revision++;
            }
            $defense->save();

            $this->notify($process, $type, $start, $location, $defenseId !== null);
        });
    }

    private function findAct(GraduationProcess $process, DefenseType $type, ?int $defenseId, ?int $revision): Defense
    {
        if ($defenseId === null) {
            if (! $this->eligibility->query($type)->whereKey($process->id)->exists()) {
                $act = mb_strtolower($type->value);
                $alreadyScheduled = $process->defenses()->pending()->where('type', $type)->exists();

                throw ValidationException::withMessages([
                    'availability' => $alreadyScheduled
                        ? "La {$act} ya está programada. Puede reprogramarla desde el calendario."
                        : "El trámite ya no cumple las condiciones para programar la {$act}.",
                ]);
            }

            return $process->defenses()->make(['type' => $type]);
        }

        $defense = $process->defenses()->lockForUpdate()->find($defenseId);

        if ($defense === null || $defense->type !== $type) {
            throw ValidationException::withMessages([
                'availability' => 'El acto seleccionado ya no está disponible para reprogramar.',
            ]);
        }

        if ($defense->is_held) {
            throw ValidationException::withMessages([
                'availability' => 'El acto seleccionado ya se realizó.',
            ]);
        }

        if ($defense->schedule_revision !== $revision) {
            throw ValidationException::withMessages([
                'availability' => 'La programación cambió desde que abrió el formulario. Consulte el calendario actualizado.',
            ]);
        }

        return $defense;
    }

    private function ensureAvailable(GraduationProcess $process, Carbon $start, ?string $location, ?int $defenseId): void
    {
        $candidate = $this->teachers($process);
        $query = $this->conflicts->overlapping($start)
            ->with(['graduationProcess.student.user', 'graduationProcess.tutor.user', 'graduationProcess.currentCommitteeMembers.teacher.user'])
            ->orderBy('scheduled_at')->orderBy('id');

        if ($defenseId !== null) {
            $query->whereKeyNot($defenseId);
        }

        $messages = [];
        foreach ($query->get() as $other) {
            $resources = array_values(array_intersect_key($candidate, $this->teachers($other->graduationProcess)));
            if ($location !== null && $other->location !== null && mb_strtolower($location) === mb_strtolower(trim($other->location))) {
                $resources[] = 'Ambiente '.$location;
            }

            if ($resources !== []) {
                $student = $other->graduationProcess->student->user->name;
                $when = $other->scheduled_at->setTimezone(FindScheduleConflicts::TIMEZONE)->format('d/m/Y H:i');
                $messages[] = implode(', ', $resources).': choque con '.mb_strtolower($other->type->value)." de {$student}, el {$when}.";
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages(['scheduled_at' => implode(' ', $messages)]);
        }
    }

    /** @return array<int, string> */
    private function teachers(GraduationProcess $process): array
    {
        $teachers = $process->currentCommitteeMembers->mapWithKeys(
            fn (CommitteeMember $member): array => [$member->teacher_id => $member->teacher->user->name],
        )->all();

        if ($process->tutor !== null) {
            $teachers[$process->tutor->id] = $process->tutor->user->name;
        }

        return $teachers;
    }

    private function notify(GraduationProcess $process, DefenseType $type, Carbon $start, ?string $location, bool $rescheduled): void
    {
        $student = $process->student->user;
        $recipients = $process->currentCommitteeMembers->map(fn (CommitteeMember $member) => $member->teacher->user)
            ->push($student, $process->tutor?->user)->filter()->unique('id');
        $act = mb_strtolower($type->value);
        $verb = $rescheduled ? 'reprogramó' : 'programó';
        $when = $start->copy()->setTimezone(FindScheduleConflicts::TIMEZONE)->format('d/m/Y H:i');
        $message = "Se {$verb} la {$act} de {$student->name} para el {$when}";
        $message .= $location === null ? '.' : ", ambiente {$location}.";

        foreach ($recipients as $recipient) {
            $this->notifyUser->handle($recipient, $message, $process);
        }
    }
}
