<?php

namespace App\Http\Requests\ProjectProfiles;

use App\Actions\ProjectProfiles\EvaluateProjectProfile;
use App\Enums\ProjectVersionStatus;
use App\Models\ProjectVersion;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EvaluateProjectProfileRequest extends FormRequest
{
    public const string APPROVE = 'aprobar';

    public const string REJECT = 'rechazar';

    /**
     * Authorizes before validating: the permission is the `can:perfiles.evaluar` route middleware (ADR-005).
     */
    public function authorize(): bool
    {
        $profile = $this->route('projectVersion');

        // UC-005 BR-001: Only the reviewer of the project profile evaluates it, before any validation.
        return $profile instanceof ProjectVersion
            && Gate::authorize('evaluate', $profile)->allowed();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([self::APPROVE, self::REJECT])],
            'observations' => ['nullable', 'array'],
            // OQ-014: Each observation has at most 2000 characters.
            'observations.*' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                // UC-005 A2: A rejection without any observation is refused, keeping what was entered.
                if ($this->input('decision') === self::REJECT
                    && $this->observationTexts() === []
                    && ! $validator->errors()->has('observations.*')) {
                    $validator->errors()->add('observations', EvaluateProjectProfile::REJECTION_NEEDS_OBSERVATION);
                }
            },
        ];
    }

    /**
     * The result the reviewer chose.
     */
    public function result(): ProjectVersionStatus
    {
        return $this->input('decision') === self::APPROVE ? ProjectVersionStatus::Approved : ProjectVersionStatus::Rejected;
    }

    /**
     * The non-blank observation texts.
     *
     * @return list<string>
     */
    public function observationTexts(): array
    {
        $texts = [];

        foreach ($this->array('observations') as $text) {
            $text = trim(is_string($text) ? $text : '');

            if ($text !== '') {
                $texts[] = $text;
            }
        }

        return $texts;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'decision' => 'resultado de la evaluación',
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
            'decision.required' => 'Elija si aprueba o rechaza el perfil de proyecto.',
            'decision.in' => 'Elija si aprueba o rechaza el perfil de proyecto.',
            'observations.array' => 'Las observaciones no tienen un formato válido.',
            'observations.*.string' => 'Cada observación debe ser un texto.',
            'observations.*.max' => 'Cada observación admite como máximo 2000 caracteres.',
        ];
    }
}
