<?php

namespace App\Actions\GraduationProcesses;

use App\Models\Student;

/**
 * Result of looking a university registration number up when a graduation process is started (UC-001 step 4).
 */
final readonly class StudentLookup
{
    public const string NotRegistered = 'not_registered';

    public const string InProgress = 'in_progress';

    public const string Approved = 'approved';

    public const string Returning = 'returning';

    /**
     * @param  string  $status  One of the status constants of this class.
     * @param  array<int, array{startedAt: string, outcome: string}>  $previousProcesses
     */
    public function __construct(
        public string $status,
        public ?Student $student = null,
        public ?string $phase = null,
        public array $previousProcesses = [],
    ) {}

    public function canStartProcess(): bool
    {
        return in_array($this->status, [self::NotRegistered, self::Returning], true);
    }
}
