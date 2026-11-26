<?php

namespace App\Actions\GraduationProcesses;

use App\Models\GraduationProcess;

/**
 * Result of starting a graduation process (UC-001 steps 10-11).
 */
final readonly class StartedGraduationProcess
{
    public function __construct(
        public GraduationProcess $process,
        public bool $accountCreated,
    ) {}
}
