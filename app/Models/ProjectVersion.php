<?php

namespace App\Models;

use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use Database\Factories\ProjectVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Archivo del proyecto de grado que el estudiante sube en una fase; el perfil de proyecto es la versión de la fase
 * evaluación de perfil.
 *
 * @property int $id
 * @property int $graduation_process_id
 * @property int $number
 * @property Phase $phase
 * @property string $title
 * @property string $file_path
 * @property string $original_file_name
 * @property int $file_size_bytes
 * @property ProjectVersionStatus $status
 * @property int|null $reviewer_id
 * @property Carbon $submitted_at
 * @property Carbon|null $evaluated_at
 */
#[Fillable([
    'graduation_process_id', 'number', 'phase', 'title', 'file_path', 'original_file_name',
    'file_size_bytes', 'status', 'reviewer_id', 'submitted_at', 'evaluated_at',
])]
class ProjectVersion extends Model
{
    /** @use HasFactory<ProjectVersionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phase' => Phase::class,
            'status' => ProjectVersionStatus::class,
            'file_size_bytes' => 'integer',
            'submitted_at' => 'datetime',
            'evaluated_at' => 'datetime',
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
     * @return BelongsTo<Teacher, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'reviewer_id');
    }

    /**
     * @return BelongsToMany<CommitteeMember, $this>
     */
    public function approvers(): BelongsToMany
    {
        return $this->belongsToMany(CommitteeMember::class, 'version_approvals')->withPivot('approved_at');
    }

    /**
     * @return HasMany<Observation, $this>
     */
    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class)->orderBy('recorded_at')->orderBy('id');
    }

    /**
     * Project profiles: the versions of the profile evaluation phase.
     *
     * @param  Builder<ProjectVersion>  $query
     */
    #[Scope]
    protected function profiles(Builder $query): void
    {
        $query->where('phase', Phase::ProfileEvaluation);
    }
}
