<?php

namespace App\Actions\GraduationProcesses;

use App\Models\GraduationProcess;
use App\Models\Student;

/**
 * Tells whether a university registration number can start a graduation process (UC-001 step 4, A1-A3).
 */
class LookupStudentForGraduationProcess
{
    public function handle(string $registrationNumber): StudentLookup
    {
        $student = Student::query()->with('user')->where('registration_number', $registrationNumber)->first();

        if ($student === null) {
            return new StudentLookup(StudentLookup::NotRegistered);
        }

        // UC-001 BR-003: A student with a process in progress cannot start another one (A1).
        $inProgress = $student->graduationProcesses()->inProgress()->first();

        if ($inProgress !== null) {
            return new StudentLookup(StudentLookup::InProgress, $student, $inProgress->phase->value);
        }

        // UC-001 BR-009: A student with an approved process already obtained the degree (A3).
        if ($student->graduationProcesses()->approved()->exists()) {
            return new StudentLookup(StudentLookup::Approved, $student);
        }

        // UC-001 BR-008: An abandoned process is not in progress, so it lists as a previous process (A2).
        $previous = $student->graduationProcesses()
            ->orderBy('started_at')
            ->get()
            ->map(fn (GraduationProcess $process): array => [
                'startedAt' => $process->started_at->toIso8601String(),
                'outcome' => $process->abandoned_at !== null ? 'Abandonado' : 'Reprobado',
            ])
            ->values()
            ->all();

        return new StudentLookup(StudentLookup::Returning, $student, previousProcesses: $previous);
    }
}
