<?php

namespace App\Actions\ProjectVersions;

use App\Models\ProjectVersion;

/**
 * The project version just submitted and whether it waits for a workshop teacher to review it (UC-004 A1).
 */
final readonly class SubmittedProjectVersion
{
    public function __construct(
        public ProjectVersion $version,
        public bool $withoutReviewer,
    ) {}
}
