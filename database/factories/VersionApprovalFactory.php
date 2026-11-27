<?php

namespace Database\Factories;

use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\ProjectVersion;
use App\Models\VersionApproval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VersionApproval>
 */
class VersionApprovalFactory extends Factory
{
    /**
     * Define the model's default state: an approval on a pre-defense version of the member's process.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $process = GraduationProcess::factory()->withCommittee()->create();
        $version = ProjectVersion::factory()->preDefenseInReview()->for($process)->create();
        $member = $process->currentCommitteeMembers->firstOrFail();

        return [
            'project_version_id' => $version->id,
            'committee_member_id' => $member->id,
            'approved_at' => now(),
        ];
    }

    /**
     * An approval on the given pre-defense version by the given committee member.
     */
    public function forVersion(ProjectVersion $version, CommitteeMember $member): static
    {
        return $this->state(fn (array $attributes) => [
            'project_version_id' => $version->id,
            'committee_member_id' => $member->id,
        ]);
    }
}
