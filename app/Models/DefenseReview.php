<?php

namespace App\Models;

use Database\Factories\DefenseReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Registro que un miembro del tribunal hace de una pre-defensa o defensa formal (UC-010).
 *
 * @property int $defense_id
 * @property int $committee_member_id
 * @property bool $has_observations
 * @property int|null $grade
 * @property Carbon $recorded_at
 * @property-read Defense $defense
 * @property-read CommitteeMember $committeeMember
 */
#[Fillable(['defense_id', 'committee_member_id', 'has_observations', 'grade', 'recorded_at'])]
class DefenseReview extends Model
{
    /** @use HasFactory<DefenseReviewFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = ['defense_id', 'committee_member_id'];

    public $incrementing = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_observations' => 'boolean',
            'grade' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Defense, $this>
     */
    public function defense(): BelongsTo
    {
        return $this->belongsTo(Defense::class);
    }

    /**
     * @return BelongsTo<CommitteeMember, $this>
     */
    public function committeeMember(): BelongsTo
    {
        return $this->belongsTo(CommitteeMember::class);
    }
}
