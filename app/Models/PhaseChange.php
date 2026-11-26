<?php

namespace App\Models;

use App\Enums\Phase;
use Database\Factories\PhaseChangeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Entrada de un trámite de titulación en una fase, con la fecha y hora en que entró.
 *
 * @property int $id
 * @property int $graduation_process_id
 * @property Phase $phase
 * @property Carbon $entered_at
 * @property int $changed_by
 */
#[Fillable(['graduation_process_id', 'phase', 'entered_at', 'changed_by'])]
class PhaseChange extends Model
{
    /** @use HasFactory<PhaseChangeFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phase' => Phase::class,
            'entered_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
