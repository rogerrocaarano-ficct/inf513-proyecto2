<?php

namespace App\Actions\GraduationProcesses;

use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Enums\RequirementDocumentType;
use App\Enums\RoleName;
use App\Models\GraduationProcess;
use App\Models\RequirementDocument;
use App\Models\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Registers the graduation process of a student, creating the student account when it does not exist (UC-001).
 */
class StartGraduationProcess
{
    public function __construct(
        private readonly LookupStudentForGraduationProcess $lookup,
        private readonly ChangePhase $changePhase,
        private readonly NotifyUser $notifyUser,
    ) {}

    /**
     * @param  array{name?: string, email?: string, id_card_number?: string, id_card_complement?: ?string, phone?: ?string}|null  $studentData  Needed unless the student is registered.
     * @param  array<string, UploadedFile>  $documents  Keyed by RequirementDocumentType value.
     */
    public function handle(
        User $assistant,
        string $registrationNumber,
        ?array $studentData,
        array $documents,
        ?Teacher $tutor,
        ?string $proposedTutorName,
    ): StartedGraduationProcess {
        $storedPaths = [];

        try {
            return DB::transaction(function () use (
                $assistant, $registrationNumber, $studentData, $documents, $tutor, $proposedTutorName, &$storedPaths,
            ): StartedGraduationProcess {
                $lookup = $this->lookup->handle($registrationNumber);

                // UC-001 BR-003: One process in progress per student; BR-009: an approved student starts no other.
                if (! $lookup->canStartProcess()) {
                    $name = $lookup->student?->user->name;

                    throw ValidationException::withMessages([
                        'registration_number' => $lookup->status === StudentLookup::Approved
                            ? "{$name} ya aprobó su trámite de titulación."
                            : "{$name} ya tiene un trámite de titulación en curso, en la fase {$lookup->phase}.",
                    ]);
                }

                // UC-001 BR-007: A registered student keeps their account and data; no account is created.
                $accountCreated = $lookup->student === null;
                $student = $lookup->student ?? $this->createStudentAccount($registrationNumber, $studentData ?? []);

                // UC-001 BR-006: With a tutor the process starts in Evaluación de perfil, without one in Requisitos.
                $phase = $tutor !== null ? Phase::ProfileEvaluation : Phase::Requirements;
                $startedAt = now();

                $process = GraduationProcess::query()->create([
                    'student_id' => $student->id,
                    'tutor_id' => $tutor?->id,
                    'proposed_tutor_name' => $tutor === null ? $proposedTutorName : null,
                    'phase' => $phase,
                    'started_at' => $startedAt,
                ]);

                $this->changePhase->handle($process, $phase, $assistant, $startedAt);

                // UC-001 BR-001: A process starts with its own three documents, one of each type.
                // UC-001 BR-002: Each document is a PDF kept on the private file storage.
                foreach (RequirementDocumentType::cases() as $type) {
                    $file = $documents[$type->value];
                    $path = (string) $file->store("requirement-documents/{$process->id}", 'local');
                    $storedPaths[] = $path;

                    RequirementDocument::query()->create([
                        'graduation_process_id' => $process->id,
                        'type' => $type,
                        'file_path' => $path,
                        'original_file_name' => $file->getClientOriginalName(),
                        'file_size_bytes' => $file->getSize(),
                        'uploaded_by' => $assistant->id,
                        'uploaded_at' => $startedAt,
                    ]);
                }

                if ($tutor !== null) {
                    $this->notifyUser->handle(
                        $tutor->user,
                        "Se le asignó como tutor del trámite de titulación de {$student->user->name}.",
                        $process,
                    );
                }

                return new StartedGraduationProcess($process, $accountCreated);
            });
        } catch (Throwable $exception) {
            // UC-001 Failure postcondition: no file of a process that was not registered remains stored.
            Storage::disk('local')->delete($storedPaths);

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createStudentAccount(string $registrationNumber, array $data): Student
    {
        // UC-001 BR-005: The initial password is the id card number without the complement, and must be changed.
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['id_card_number'],
            'is_active' => true,
            'must_change_password' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->attach(Role::query()->where('name', RoleName::Student)->firstOrFail());

        $student = Student::query()->create([
            'user_id' => $user->id,
            'registration_number' => $registrationNumber,
            'id_card_number' => $data['id_card_number'],
            'id_card_complement' => $data['id_card_complement'] ?? null,
            // OQ-052: The phone is stored as its 7 or 8 digits, without the +591 prefix.
            'phone' => isset($data['phone']) ? preg_replace('/^\+591/', '', $data['phone']) : null,
        ]);

        return $student->setRelation('user', $user);
    }
}
