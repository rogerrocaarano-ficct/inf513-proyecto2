<?php

namespace App\Models;

use App\Enums\RequirementDocumentType;
use Database\Factories\RequirementDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Documento de requisitos del estudiante adjuntado al trámite de titulación.
 *
 * @property int $id
 * @property int $graduation_process_id
 * @property RequirementDocumentType $type
 * @property string $file_path
 * @property string $original_file_name
 * @property int $file_size_bytes
 * @property int $uploaded_by
 * @property Carbon $uploaded_at
 */
#[Fillable([
    'graduation_process_id', 'type', 'file_path', 'original_file_name',
    'file_size_bytes', 'uploaded_by', 'uploaded_at',
])]
class RequirementDocument extends Model
{
    /** @use HasFactory<RequirementDocumentFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RequirementDocumentType::class,
            'file_size_bytes' => 'integer',
            'uploaded_at' => 'datetime',
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
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
