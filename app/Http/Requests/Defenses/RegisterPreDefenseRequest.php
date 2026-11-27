<?php

namespace App\Http\Requests\Defenses;

use App\Enums\PreDefenseDecision;
use App\Models\Defense;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterPreDefenseRequest extends FormRequest
{
    /**
     * Authorizes before validating: the permission is the `can:` route middleware (ADR-005).
     */
    public function authorize(): bool
    {
        $defense = $this->route('defense');

        // UC-010 BR-001, NFR-004: only a current member of this tribunal registers the act.
        return $defense instanceof Defense
            && Gate::inspect('registerPreDefense', $defense)->allowed();
    }

    /**
     * UC-010 A5: a member no longer enabled is told the reason, in Spanish, instead of a bare refusal.
     *
     * @throws ValidationException
     */
    protected function failedAuthorization(): void
    {
        $defense = $this->route('defense');

        throw ValidationException::withMessages([
            'defense' => $defense instanceof Defense
                ? (string) Gate::inspect('registerPreDefense', $defense)->message()
                : 'No puede registrar esta pre-defensa.',
        ]);
    }

    /**
     * UC-010 A3 and BR-005: blank observation texts do not satisfy the requirement to register observations.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (
                $this->input('decision') === PreDefenseDecision::Observations->value
                && ! $validator->errors()->has('observations')
                && collect($this->input('observations', []))
                    ->filter(fn (mixed $text): bool => is_string($text) && trim($text) !== '')
                    ->isEmpty()
            ) {
                $validator->errors()->add('observations', 'Registre al menos una observación.');
            }
        });
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(PreDefenseDecision::class)],
            // UC-010 A3 and BR-005: observations are required only when the member chooses to register them.
            'observations' => ['nullable', 'array', 'required_if:decision,'.PreDefenseDecision::Observations->value],
            'observations.*' => ['required', 'string', 'max:2000'],
            // UC-010 step 6: the confirmed schedule is checked against the stored revision (as in UC-009).
            'schedule_revision' => ['required', 'integer', 'min:1'],
        ];
    }

    public function decision(): ?PreDefenseDecision
    {
        $value = $this->validated('decision');

        return $value === null ? null : PreDefenseDecision::from($value);
    }

    /** @return list<string> */
    public function observationTexts(): array
    {
        return array_values(array_map(
            fn (string $text): string => trim($text),
            $this->validated('observations') ?? [],
        ));
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'decision' => 'decisión',
            'observations' => 'observaciones',
            'observations.*' => 'observación',
            'schedule_revision' => 'revisión de la programación',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'decision.required' => 'Elija si registra observaciones, que no las hubo o la inasistencia del estudiante.',
            'decision.enum' => 'La decisión elegida no es válida.',
            'observations.array' => 'Las observaciones no tienen un formato válido.',
            'observations.required_if' => 'Registre al menos una observación.',
            'observations.*.required' => 'La observación no puede estar vacía.',
            'observations.*.string' => 'Cada observación debe ser un texto.',
            'observations.*.max' => 'Cada observación admite como máximo 2000 caracteres.',
            'schedule_revision.required' => 'La revisión de la programación es obligatoria.',
            'schedule_revision.integer' => 'La revisión de la programación debe ser un número entero.',
            'schedule_revision.min' => 'La revisión de la programación debe ser al menos 1.',
        ];
    }
}
