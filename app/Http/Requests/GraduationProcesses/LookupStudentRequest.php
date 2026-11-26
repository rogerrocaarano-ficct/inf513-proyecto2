<?php

namespace App\Http\Requests\GraduationProcesses;

use Illuminate\Foundation\Http\FormRequest;

class LookupStudentRequest extends FormRequest
{
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
            'registration_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['registration_number' => 'número de registro universitario'];
    }
}
