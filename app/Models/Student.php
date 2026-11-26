<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Persona inscrita en la carrera que elabora un proyecto de grado para titularse.
 *
 * @property int $id
 * @property int $user_id
 * @property string $registration_number
 * @property string $id_card_number
 * @property string|null $id_card_complement
 * @property string|null $phone
 * @property-read User $user
 */
#[Fillable(['user_id', 'registration_number', 'id_card_number', 'id_card_complement', 'phone'])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    public $timestamps = false;

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
    public function graduationProcesses(): HasMany
    {
        return $this->hasMany(GraduationProcess::class);
    }
}
