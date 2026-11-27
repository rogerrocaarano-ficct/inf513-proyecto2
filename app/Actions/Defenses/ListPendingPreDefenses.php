<?php

namespace App\Actions\Defenses;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Models\Defense;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Lists the pre-defenses a teacher still has to register: started, pending and not registered by them (UC-010).
 */
class ListPendingPreDefenses
{
    /**
     * @return Collection<int, Defense>
     */
    public function handle(Teacher $teacher): Collection
    {
        return Defense::query()
            ->pending()
            ->where('type', DefenseType::PreDefense)
            // UC-010 BR-002: The act can only be registered after its scheduled start.
            ->where('scheduled_at', '<=', now())
            ->whereHas('graduationProcess', fn (Builder $processes) => $processes
                ->inProgress()
                ->where('phase', Phase::PreDefense))
            // UC-010 BR-001: Only a current member of the tribunal sees the act, and only until they register it.
            ->whereHas('graduationProcess.currentCommitteeMembers', fn (Builder $members) => $members
                ->where('teacher_id', $teacher->id)
                ->whereDoesntHave('reviews', fn (Builder $reviews) => $reviews
                    ->whereColumn('defense_reviews.defense_id', 'defenses.id')))
            ->with([
                'graduationProcess.student.user',
                'graduationProcess.tutor.user',
                'graduationProcess.currentCommitteeMembers.teacher.user',
                'reviews',
            ])
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();
    }
}
