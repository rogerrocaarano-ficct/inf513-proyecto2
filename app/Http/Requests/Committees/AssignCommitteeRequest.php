<?php

namespace App\Http\Requests\Committees;

use Illuminate\Foundation\Http\FormRequest;

class AssignCommitteeRequest extends FormRequest
{
    /**
     * Authorization is the `can:tribunales.asignar` route middleware (ADR-005).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The count and distinctness of the teachers is a business rule of the Action (UC-006 BR-002).
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'teacher_ids' => 'docentes del tribunal evaluador',
            'teacher_ids.*' => 'docente del tribunal evaluador',
        ];
    }
}
