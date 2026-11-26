<?php

namespace App\Models;

use Database\Factories\CommitteeMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Docente asignado al tribunal evaluador de un trámite de titulación.
 *
 * @property int $id
 * @property int $graduation_process_id
 * @property int $teacher_id
 * @property Carbon $assigned_at
 * @property Carbon|null $replaced_at
 * @property-read Teacher $teacher
 * @property-read GraduationProcess $graduationProcess
 */
#[Fillable(['graduation_process_id', 'teacher_id', 'assigned_at', 'replaced_at'])]
class CommitteeMember extends Model
{
    /** @use HasFactory<CommitteeMemberFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'replaced_at' => 'datetime',
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
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Members who were not replaced.
     *
     * @param  Builder<CommitteeMember>  $query
     */
    #[Scope]
    protected function current(Builder $query): void
    {
        $query->whereNull('replaced_at');
    }
}
