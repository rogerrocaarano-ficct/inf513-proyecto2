<?php

namespace App\Actions\ProjectVersions;

use App\Enums\Phase;
use App\Models\GraduationProcess;
use App\Models\ProjectVersion;

/**
 * What the current phase of a student's process allows: the process and, when it admits no new version, why.
 */
final readonly class VersionAdmission
{
    public function __construct(
        public ?GraduationProcess $process,
        public ?string $reason,
        public ?ProjectVersion $lastVersion,
    ) {}

    public function isAdmitted(): bool
    {
        return $this->reason === null;
    }

    /**
     * UC-004 BR-007: Only a project profile carries a title entered by the student.
     */
    public function requiresTitle(): bool
    {
        return $this->isAdmitted() && $this->process?->phase === Phase::ProfileEvaluation;
    }
}
