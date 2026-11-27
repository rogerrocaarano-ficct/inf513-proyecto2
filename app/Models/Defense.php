<?php

namespace App\Models;

use App\Enums\DefenseType;
use Carbon\CarbonInterface;
use Database\Factories\DefenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pre-defensa o defensa formal programada de un proyecto de grado.
 *
 * @property int $id
 * @property int $graduation_process_id
 * @property DefenseType $type
 * @property CarbonInterface $scheduled_at
 * @property string|null $location
 * @property int $schedule_revision
 * @property int|null $grade
 * @property bool $is_held
 * @property bool $is_student_absent
 * @property int|null $absence_recorded_by_id
 * @property-read GraduationProcess $graduationProcess
 * @property-read CommitteeMember|null $absenceRecordedBy
 */
#[Fillable([
    'graduation_process_id', 'type', 'scheduled_at', 'location', 'grade',
    'is_held', 'is_student_absent', 'absence_recorded_by_id',
])]
class Defense extends Model
{
    /** @use HasFactory<DefenseFactory> */
    use HasFactory;

    /** Duración de cada pre-defensa o defensa formal, en horas (UC-009 BR-003). */
    public const int DURATION_HOURS = 2;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DefenseType::class,
            'scheduled_at' => 'datetime',
            'grade' => 'integer',
            'is_held' => 'boolean',
            'is_student_absent' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<GraduationProcess, $this>
     */
    public function graduationProcess(): BelongsTo
    {
        return $this->belongsTo(GraduationProcess::class);
    }

    /**
     * @return BelongsTo<CommitteeMember, $this>
     */
    public function absenceRecordedBy(): BelongsTo
    {
        return $this->belongsTo(CommitteeMember::class, 'absence_recorded_by_id');
    }

    /**
     * The individual registrations of the committee members (UC-010).
     *
     * @return HasMany<DefenseReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(DefenseReview::class);
    }

    /**
     * The committee members who registered this act, through defense_reviews (UC-010, UC-011).
     *
     * @return BelongsToMany<CommitteeMember, $this>
     */
    public function committeeMembers(): BelongsToMany
    {
        return $this->belongsToMany(CommitteeMember::class, 'defense_reviews', 'defense_id', 'committee_member_id')
            ->withPivot(['has_observations', 'grade', 'recorded_at']);
    }

    /**
     * The observations the committee members recorded on this pre-defense.
     *
     * @return HasMany<Observation, $this>
     */
    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class)->orderBy('recorded_at')->orderBy('id');
    }

    /**
     * Pre-defenses and formal defenses scheduled and not yet held.
     *
     * @param  Builder<Defense>  $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('is_held', false);
    }
}
