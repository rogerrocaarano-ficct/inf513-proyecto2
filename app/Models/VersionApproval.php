<?php

namespace App\Models;

use Database\Factories\VersionApprovalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Aprobación que un miembro del tribunal registra sobre una versión de la fase pre-defensa (UC-008).
 *
 * @property int $project_version_id
 * @property int $committee_member_id
 * @property Carbon $approved_at
 * @property-read ProjectVersion $projectVersion
 * @property-read CommitteeMember $committeeMember
 */
#[Fillable(['project_version_id', 'committee_member_id', 'approved_at'])]
class VersionApproval extends Model
{
    /** @use HasFactory<VersionApprovalFactory> */
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ProjectVersion, $this>
     */
    public function projectVersion(): BelongsTo
    {
        return $this->belongsTo(ProjectVersion::class);
    }

    /**
     * @return BelongsTo<CommitteeMember, $this>
     */
    public function committeeMember(): BelongsTo
    {
        return $this->belongsTo(CommitteeMember::class);
    }
}
