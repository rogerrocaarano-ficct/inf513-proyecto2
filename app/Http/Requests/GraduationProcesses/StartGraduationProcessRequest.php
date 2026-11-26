<?php

namespace App\Http\Requests\GraduationProcesses;

use App\Actions\GraduationProcesses\LookupStudentForGraduationProcess;
use App\Actions\GraduationProcesses\StudentLookup;
use App\Models\Student;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartGraduationProcessRequest extends FormRequest
{
    private ?StudentLookup $lookup = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * The result of looking the registration number up.
     */
    public function studentLookup(): StudentLookup
    {
        return $this->lookup ??= app(LookupStudentForGraduationProcess::class)
            ->handle((string) $this->input('registration_number'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isNewStudent = $this->studentLookup()->status === StudentLookup::NotRegistered;
        $isReturningStudent = $this->studentLookup()->status === StudentLookup::Returning;

        $documentRules = ['required', 'file', 'mimes:pdf', 'max:20480'];

        return [
            'registration_number' => ['required', 'string', 'max:20'],
            // UC-001 A2: The assistant confirms that the registered student is the one who presents.
            'confirmed_student' => $isReturningStudent ? ['required', 'accepted'] : ['nullable'],
            'name' => [Rule::requiredIf($isNewStudent), 'nullable', 'string', 'max:255'],
            // UC-001 BR-004: One email identifies one user account (A7).
            'email' => [
                Rule::requiredIf($isNewStudent), 'nullable', 'string', 'email', 'max:255',
                Rule::unique('users', 'email'),
            ],
            // UC-001 BR-004: The id card number with its complement identifies one student (A7).
            'id_card_number' => [
                Rule::requiredIf($isNewStudent), 'nullable', 'string', 'max:20', 'regex:/^\d+$/',
                $this->idCardIsUnique(...),
            ],
            // OQ-048: The complement accepts letters and digits only.
            'id_card_complement' => ['nullable', 'string', 'max:5', 'regex:/^[0-9A-Za-z]+$/'],
            // UC-001 A8, OQ-039: The phone is optional: 7 or 8 digits with an optional +591 prefix.
            'phone' => ['nullable', 'string', 'regex:/^(\+591)?\d{7,8}$/'],
            'enrollment_slip' => $documentRules,
            'curriculum_vitae' => $documentRules,
            'request_letter' => $documentRules,
            // UC-001 BR-006: Only an active teacher who is not the workshop teacher can be tutor.
            'tutor_id' => [
                'nullable', 'integer',
                Rule::exists('teachers', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->where('is_workshop_teacher', false)),
            ],
            'proposed_tutor_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @param  Closure(string): mixed  $fail
     */
    private function idCardIsUnique(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->studentLookup()->status !== StudentLookup::NotRegistered || $value === null) {
            return;
        }

        $complement = $this->input('id_card_complement');

        $exists = Student::query()
            ->where('id_card_number', $value)
            ->when(
                $complement === null || $complement === '',
                fn ($query) => $query->whereNull('id_card_complement'),
                fn ($query) => $query->where('id_card_complement', $complement),
            )
            ->exists();

        if ($exists) {
            $fail('La cédula de identidad ya pertenece a otro estudiante.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'registration_number' => 'número de registro universitario',
            'confirmed_student' => 'confirmación del estudiante',
            'name' => 'nombre completo',
            'email' => 'correo electrónico',
            'id_card_number' => 'número de cédula de identidad',
            'id_card_complement' => 'complemento',
            'phone' => 'teléfono',
            'enrollment_slip' => 'boleta de inscripción',
            'curriculum_vitae' => 'hoja de vida',
            'request_letter' => 'carta de solicitud',
            'tutor_id' => 'tutor',
            'proposed_tutor_name' => 'nombre del tutor propuesto',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'email.unique' => 'El correo electrónico ya pertenece a otro usuario.',
            'phone.regex' => 'El teléfono debe tener 7 u 8 dígitos, con el prefijo +591 opcional.',
            'id_card_number.regex' => 'El número de cédula de identidad solo puede tener dígitos.',
            'id_card_complement.regex' => 'El complemento solo puede tener letras y dígitos.',
            'confirmed_student.required' => 'Confirme que es el estudiante que se presenta.',
            'confirmed_student.accepted' => 'Confirme que es el estudiante que se presenta.',
            'tutor_id.exists' => 'El tutor elegido no es un docente elegible.',
        ];

        // UC-001 A9: A document is missing, is not a PDF, or exceeds 20 MB.
        foreach (['enrollment_slip' => 'boleta de inscripción', 'curriculum_vitae' => 'hoja de vida', 'request_letter' => 'carta de solicitud'] as $field => $document) {
            $messages["{$field}.required"] = "Adjunte la {$document}.";
            $messages["{$field}.file"] = 'El archivo debe ser PDF.';
            $messages["{$field}.mimes"] = 'El archivo debe ser PDF.';
            $messages["{$field}.max"] = 'El archivo no debe superar los 20 MB.';
            $messages["{$field}.uploaded"] = 'El archivo no debe superar los 20 MB.';
        }

        return $messages;
    }
}
