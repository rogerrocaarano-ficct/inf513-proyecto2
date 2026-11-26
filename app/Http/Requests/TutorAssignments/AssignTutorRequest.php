<?php

namespace App\Http\Requests\TutorAssignments;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('tutores.asignar');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['teacher_id' => 'docente'];
    }
}
