<?php

namespace App\Actions\GraduationProcesses;

use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Models\GraduationProcess;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Archives an ended graduation process and preserves its records (UC-012). */
class ArchiveGraduationProcess
{
    public function __construct(private ChangePhase $changePhase, private NotifyUser $notifyUser) {}

    public function handle(GraduationProcess $process, User $assistant): void
    {
        DB::transaction(function () use ($process, $assistant): void {
            $locked = GraduationProcess::query()->lockForUpdate()->findOrFail($process->id);
            $endedAt = $locked->concluded_at ?? $locked->abandoned_at;
            $archivedAt = now()->startOfSecond();

            $reason = match (true) {
                $locked->archived_at !== null => 'El trámite de titulación ya fue archivado.',
                $endedAt === null => 'El trámite de titulación sigue en curso y todavía no se puede archivar.',
                $endedAt->gte($archivedAt) => 'El archivo debe registrarse después de la conclusión o el abandono del trámite de titulación.',
                default => null,
            };

            if ($reason !== null) {
                throw ValidationException::withMessages(['availability' => $reason]);
            }

            // The database requires the archive phase and its timestamp to be set in the same update.
            $locked->forceFill(['archived_at' => $archivedAt]);
            $this->changePhase->handle($locked, Phase::Archive, $assistant, $archivedAt);

            $this->notifyUser->handle($locked->student->user, 'Su trámite de titulación fue archivado.', $locked);
        });
    }
}
