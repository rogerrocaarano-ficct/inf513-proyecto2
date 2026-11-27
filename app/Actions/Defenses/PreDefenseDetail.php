<?php

namespace App\Actions\Defenses;

use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\ProjectVersion;
use Illuminate\Support\Collection;

/**
 * What a committee member sees when they choose a pre-defense to register (UC-010 step 4).
 */
final readonly class PreDefenseDetail
{
    /**
     * @param  Collection<int, CommitteeMember>  $reportedBy
     */
    public function __construct(
        public Defense $defense,
        public ?ProjectVersion $version,
        public Collection $reportedBy,
    ) {}
}
