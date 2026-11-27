<?php

namespace App\Actions\Defenses;

use App\Actions\Notifications\NotifyUser;
use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Registers an individual grade or the student's absence (UC-011). */
class RegisterFormalDefense
{
    public const string UNAVAILABLE = 'El registro de la defensa formal ya no está disponible.';

    public function __construct(private NotifyUser $notifyUser) {}

    public function handle(User $user, Defense $defense, int $revision, bool $absent, mixed $grade): string
    {
        return DB::transaction(function () use ($user, $defense, $revision, $absent, $grade): string {
            // Lock in the same order as scheduling, committee replacement and version submission.
            $process = GraduationProcess::query()->lockForUpdate()->findOrFail($defense->graduation_process_id);
            $act = $process->defenses()->lockForUpdate()->findOrFail($defense->id);
            $members = $process->currentCommitteeMembers()->with('teacher.user')->get();
            $member = $members->firstWhere('teacher_id', $user->teacher?->id);

            if ($process->concluded_at !== null || $process->abandoned_at !== null
                || $process->phase !== Phase::FormalDefense || $act->type !== DefenseType::FormalDefense
                || $act->is_held || $act->schedule_revision !== $revision || ! $act->scheduled_at->isPast()
                || $member === null || $act->reviews()->where('committee_member_id', $member->id)->exists()
                || ($absent && $act->reviews()->exists())) {
                throw ValidationException::withMessages(['availability' => self::UNAVAILABLE]);
            }

            $recordedAt = now();

            if ($absent) {
                $act->update([
                    'is_held' => true,
                    'is_student_absent' => true,
                    'absence_recorded_by_id' => $member->id,
                    'grade' => null,
                ]);
                $process->update(['abandoned_at' => $recordedAt]);
                $message = 'El trámite de titulación quedó abandonado por inasistencia a la defensa formal.';
            } else {
                if (! is_int($grade) && ! is_string($grade)) {
                    $grade = null;
                }

                $value = validator(['grade' => $grade], ['grade' => ['required', 'integer', 'between:0,100']], [
                    'grade.*' => 'Ingrese una calificación entera entre 0 y 100.',
                ])->validate()['grade'];

                $act->reviews()->create([
                    'committee_member_id' => $member->id,
                    'has_observations' => false,
                    'grade' => (int) $value,
                    'recorded_at' => $recordedAt,
                ]);

                $grades = $act->reviews()->whereIn('committee_member_id', $members->modelKeys())
                    ->whereNotNull('grade')->pluck('grade');

                if ($members->count() !== 3 || $grades->count() !== 3) {
                    return 'Se registró su calificación. La defensa formal sigue pendiente de otras calificaciones.';
                }

                $jointGrade = (int) round($grades->sum() / 3, 0, PHP_ROUND_HALF_UP);
                $act->update(['is_held' => true, 'grade' => $jointGrade]);
                $process->update(['concluded_at' => $recordedAt]);

                $result = $jointGrade >= GraduationProcess::PASSING_GRADE ? 'aprobado' : 'reprobado';
                $message = "La defensa formal se realizó con {$jointGrade} de 100. El trámite de titulación concluyó {$result}.";
            }

            $recipients = $members
                ->reject(fn (CommitteeMember $recipient): bool => $absent && $recipient->id === $member->id)
                ->map(fn (CommitteeMember $recipient): User => $recipient->teacher->user)
                ->push($process->student->user, $process->tutor?->user)
                ->filter()
                ->unique('id');

            foreach ($recipients as $recipient) {
                $this->notifyUser->handle($recipient, $message, $process);
            }

            return $message;
        });
    }
}
