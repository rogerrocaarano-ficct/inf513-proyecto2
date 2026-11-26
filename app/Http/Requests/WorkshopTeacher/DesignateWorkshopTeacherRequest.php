<?php

namespace App\Http\Requests\WorkshopTeacher;

use App\Enums\PermissionKey;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignateWorkshopTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionKey::DesignateWorkshopTeacher->value) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => [
                'required',
                'integer',
                // UC-003 BR-002: Only an active teacher who is not already the workshop teacher is eligible.
                Rule::exists('teachers', 'id')->where('is_active', true)->where('is_workshop_teacher', false),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['teacher_id' => 'docente'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Elija al docente de taller de grado.',
            'teacher_id.integer' => 'Elija al docente de taller de grado.',
            'teacher_id.exists' => 'El docente elegido no puede ser designado como docente de taller de grado.',
        ];
    }
}
