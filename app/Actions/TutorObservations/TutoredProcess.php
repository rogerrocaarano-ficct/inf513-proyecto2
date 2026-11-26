<?php

namespace App\Actions\TutorObservations;

use App\Models\GraduationProcess;
use App\Models\ProjectVersion;

/**
 * A process in progress of a tutor with its last project version.
 */
readonly class TutoredProcess
{
    public function __construct(
        public GraduationProcess $process,
        public ?ProjectVersion $lastVersion,
        public bool $hasUnobservedVersion,
    ) {}
}
