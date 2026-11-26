<?php

namespace App\Models;

use Database\Factories\ObservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Señalamiento sobre una versión del proyecto o sobre una pre-defensa.
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $project_version_id
 * @property int|null $defense_id
 * @property string $body
 * @property Carbon $recorded_at
 */
#[Fillable(['author_id', 'project_version_id', 'defense_id', 'body', 'recorded_at'])]
class Observation extends Model
{
    /** @use HasFactory<ObservationFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * The reviewer, tutor or committee member who recorded the observation.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<ProjectVersion, $this>
     */
    public function projectVersion(): BelongsTo
    {
        return $this->belongsTo(ProjectVersion::class);
    }

    /**
     * @return BelongsTo<Defense, $this>
     */
    public function defense(): BelongsTo
    {
        return $this->belongsTo(Defense::class);
    }
}
