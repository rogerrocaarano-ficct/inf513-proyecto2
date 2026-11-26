<?php

namespace App\Models;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Profesional que enseña en la carrera y puede ser tutor, revisor o miembro del tribunal.
 *
 * @property int $id
 * @property int $user_id
 * @property bool $is_workshop_teacher
 * @property bool $is_active
 * @property-read User $user
 */
#[Fillable(['user_id', 'is_workshop_teacher', 'is_active'])]
class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_workshop_teacher' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<GraduationProcess, $this>
     */
    public function tutoredProcesses(): HasMany
    {
        return $this->hasMany(GraduationProcess::class, 'tutor_id');
    }

    /**
     * @return HasMany<CommitteeMember, $this>
     */
    public function committeeMemberships(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    /**
     * @return HasMany<ProjectVersion, $this>
     */
    public function reviewedVersions(): HasMany
    {
        return $this->hasMany(ProjectVersion::class, 'reviewer_id');
    }

    /**
     * @param  Builder<Teacher>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Teacher>  $query
     */
    #[Scope]
    protected function workshopTeacher(Builder $query): void
    {
        $query->where('is_workshop_teacher', true);
    }

    /**
     * Teachers who can be the tutor of a graduation process, or of the given one.
     *
     * @param  Builder<Teacher>  $query
     */
    #[Scope]
    protected function eligibleAsTutor(Builder $query, ?GraduationProcess $process = null): void
    {
        // UC-001 BR-006: Only an active teacher who is not the workshop teacher can be tutor.
        // UC-002 BR-001: Eligible tutor: active teacher, not the workshop teacher...
        $query->where('is_active', true)->where('is_workshop_teacher', false);

        if ($process === null) {
            return;
        }

        // UC-002 BR-001: ...not its current committee member (OQ-041) and not already its tutor.
        $query
            ->when($process->tutor_id, fn (Builder $query, int $tutorId) => $query->whereKeyNot($tutorId))
            ->whereDoesntHave('committeeMemberships', fn (Builder $query) => $query
                ->where('graduation_process_id', $process->getKey())
                ->whereNull('replaced_at'));
    }
}
