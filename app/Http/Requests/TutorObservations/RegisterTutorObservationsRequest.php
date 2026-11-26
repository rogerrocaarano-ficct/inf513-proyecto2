<?php

namespace App\Http\Requests\TutorObservations;

use App\Models\ProjectVersion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegisterTutorObservationsRequest extends FormRequest
{
    /**
     * Authorizes before validating: the permission is the `can:versiones.observar` route middleware (ADR-005).
     */
    public function authorize(): bool
    {
        $version = $this->route('projectVersion');

        // UC-007 BR-001: Only the current tutor of a process in progress observes its versions.
        return $version instanceof ProjectVersion
            && Gate::inspect('observe', $version)->allowed();
    }

    /**
     * UC-007 A4: A tutor no longer enabled is told the reason, in Spanish, instead of a bare refusal.
     *
     * @throws ValidationException
     */
    protected function failedAuthorization(): void
    {
        $version = $this->route('projectVersion');

        throw ValidationException::withMessages([
            'project_version' => $version instanceof ProjectVersion
                ? (string) Gate::inspect('observe', $version)->message()
                : 'No puede observar esta versión del proyecto.',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'observations' => ['required', 'array', 'min:1'],
            // UC-007 A1 BR-004: Each observation is not empty and has at most 2000 characters.
            'observations.*' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return list<string>
     */
    public function observationTexts(): array
    {
        return array_values(array_map(
            fn (string $text): string => trim($text),
            $this->validated('observations'),
        ));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'observations' => 'observaciones',
            'observations.*' => 'observación',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'observations.required' => 'Escriba al menos una observación.',
            'observations.array' => 'Las observaciones no tienen un formato válido.',
            'observations.min' => 'Escriba al menos una observación.',
            'observations.*.required' => 'La observación no puede estar vacía.',
            'observations.*.string' => 'Cada observación debe ser un texto.',
            'observations.*.max' => 'Cada observación admite como máximo 2000 caracteres.',
        ];
    }
}
