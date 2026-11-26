<?php

namespace App\Http\Requests\Committees;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceCommitteeMemberRequest extends FormRequest
{
    /**
     * Authorization is the `can:tribunales.asignar` route middleware (ADR-005).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'committee_member_id' => ['required', 'integer'],
            'teacher_id' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'committee_member_id' => 'miembro que se reemplaza',
            'teacher_id' => 'docente que entra',
        ];
    }
}
