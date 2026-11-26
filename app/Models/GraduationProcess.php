<?php

namespace App\Models;

use App\Enums\DefenseType;
use App\Enums\Phase;
use Database\Factories\GraduationProcessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Proceso de un estudiante para titularse por Proyecto de Grado, desde su registro hasta el archivo.
 *
 * @property int $id
 * @property int $student_id
 * @property int|null $tutor_id
 * @property string|null $proposed_tutor_name
 * @property string|null $project_title
 * @property Phase $phase
 * @property Carbon $started_at
 * @property Carbon|null $concluded_at
 * @property Carbon|null $abandoned_at
 * @property Carbon|null $archived_at
 * @property-read Student $student
 * @property-read Teacher|null $tutor
 */
#[Fillable([
    'student_id', 'tutor_id', 'proposed_tutor_name', 'project_title', 'phase',
    'started_at', 'concluded_at', 'abandoned_at', 'archived_at',
])]
class GraduationProcess extends Model
{
    /** @use HasFactory<GraduationProcessFactory> */
    use HasFactory;

    /** Calificación mínima con la que la defensa formal se aprueba (C-016). */
    public const int PASSING_GRADE = 51;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phase' => Phase::class,
            'started_at' => 'datetime',
            'concluded_at' => 'datetime',
            'abandoned_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'tutor_id');
    }

    /**
     * @return HasMany<PhaseChange, $this>
     */
    public function phaseChanges(): HasMany
    {
        return $this->hasMany(PhaseChange::class)->orderBy('entered_at');
    }

    /**
     * @return HasMany<RequirementDocument, $this>
     */
    public function requirementDocuments(): HasMany
    {
        return $this->hasMany(RequirementDocument::class);
    }

    /**
     * @return HasMany<ProjectVersion, $this>
     */
    public function projectVersions(): HasMany
    {
        return $this->hasMany(ProjectVersion::class)->orderBy('number');
    }

    /**
     * Every committee member the process ever had, replaced ones included.
     *
     * @return HasMany<CommitteeMember, $this>
     */
    public function committeeMembers(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    /**
     * The committee members who were not replaced.
     *
     * @return HasMany<CommitteeMember, $this>
     */
    public function currentCommitteeMembers(): HasMany
    {
        return $this->hasMany(CommitteeMember::class)->whereNull('replaced_at');
    }

    /**
     * @return HasMany<Defense, $this>
     */
    public function defenses(): HasMany
    {
        return $this->hasMany(Defense::class);
    }

    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Processes that neither concluded nor were abandoned.
     *
     * @param  Builder<GraduationProcess>  $query
     */
    #[Scope]
    protected function inProgress(Builder $query): void
    {
        // UC-001 BR-003: A process stops being in progress when it concludes or is abandoned.
        $query->whereNull('concluded_at')->whereNull('abandoned_at');
    }

    /**
     * Processes that concluded with a formal defense graded 51 or more.
     *
     * @param  Builder<GraduationProcess>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        // UC-001 BR-009: An approved process concluded with a formal defense of 51 or more (C-016).
        $query->whereNotNull('concluded_at')
            ->whereHas('defenses', fn (Builder $query) => $query
                ->where('type', DefenseType::FormalDefense)
                ->where('is_held', true)
                ->where('grade', '>=', self::PASSING_GRADE));
    }
}
