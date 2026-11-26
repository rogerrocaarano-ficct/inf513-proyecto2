<?php

namespace App\Http\Requests\Defenses;

use App\Enums\DefenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleDefenseRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'process_id' => ['required', 'integer', 'min:1'],
            'type' => ['required', Rule::enum(DefenseType::class)],
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'location' => ['nullable', 'string', 'max:100'],
            'defense_id' => ['nullable', 'required_with:schedule_revision', 'integer', 'min:1'],
            'schedule_revision' => ['nullable', 'required_with:defense_id', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'process_id' => 'trámite de titulación',
            'type' => 'tipo de acto',
            'scheduled_at' => 'fecha y hora',
            'location' => 'ambiente',
            'defense_id' => 'acto programado',
            'schedule_revision' => 'revisión de la programación',
        ];
    }
}
