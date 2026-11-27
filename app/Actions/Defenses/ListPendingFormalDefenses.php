<?php

namespace App\Actions\Defenses;

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Models\Defense;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

/** Finds defenses that still await this teacher's grade (UC-011). */
class ListPendingFormalDefenses
{
    /** @return Builder<Defense> */
    public function query(Teacher $teacher): Builder
    {
        return Defense::query()->pending()
            ->where('type', DefenseType::FormalDefense)
            ->where('scheduled_at', '<', now())
            ->whereHas('graduationProcess', fn (Builder $processes) => $processes
                ->inProgress()->where('phase', Phase::FormalDefense))
            ->whereHas('graduationProcess.currentCommitteeMembers', fn (Builder $members) => $members
                ->where('teacher_id', $teacher->id)
                ->whereDoesntHave('reviews', fn (Builder $reviews) => $reviews
                    ->whereColumn('defense_reviews.defense_id', 'defenses.id')));
    }
}
