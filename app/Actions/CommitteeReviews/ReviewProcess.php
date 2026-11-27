<?php

namespace App\Actions\CommitteeReviews;

use App\Models\GraduationProcess;
use App\Models\ProjectVersion;

/**
 * A process in progress of a current committee member with its pending and last project versions (UC-008 step 2).
 */
readonly class ReviewProcess
{
    public function __construct(
        public GraduationProcess $process,
        public ?ProjectVersion $pendingVersion,
        public ?ProjectVersion $lastVersion,
    ) {}

    /**
     * Whether this process has a pre-defense version waiting for the member's pronouncement.
     */
    public function hasPendingVersion(): bool
    {
        return $this->pendingVersion !== null;
    }
}
