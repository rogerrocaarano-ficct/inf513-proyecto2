<?php

namespace App\Http\Requests\CommitteeReviews;

use App\Actions\CommitteeReviews\RegisterCommitteePronouncement;
use App\Models\CommitteeMember;
use App\Models\ProjectVersion;
use App\Policies\ProjectVersionPolicy;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Validates and authorizes the pronouncement of a committee member on a pre-defense version (UC-008).
 */
class RegisterCommitteePronouncementRequest extends FormRequest
{
    public const string APPROVE = 'aprobar';

    public const string OBSERVE = 'observar';

    private const string EMPTY_OBSERVATION = 'Escriba la observación.';

    /**
     * Authorizes before validating: the permission is the `can:versiones.revisar` route middleware (ADR-005).
     */
    public function authorize(): bool
    {
        $version = $this->route('projectVersion');

        // UC-008 BR-001: Only a current member of the version's committee reviews it.
        return $version instanceof ProjectVersion
            && Gate::inspect('review', $version)->allowed();
    }

    /**
     * UC-008 BR-001, A6: A non-member and a replaced member are told why, in Spanish.
     *
     * @throws ValidationException
     */
    protected function failedAuthorization(): void
    {
        $version = $this->route('projectVersion');
        $teacherId = $this->user()?->teacher?->id;
        $wasMember = $version instanceof ProjectVersion
            && $teacherId !== null
            && CommitteeMember::query()
                ->where('graduation_process_id', $version->graduation_process_id)
                ->where('teacher_id', $teacherId)
                ->exists();

        throw ValidationException::withMessages([
            'project_version' => $wasMember
                ? RegisterCommitteePronouncement::CANNOT_REGISTER
                : ProjectVersionPolicy::NOT_CURRENT_MEMBER,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([self::APPROVE, self::OBSERVE])],
            'observations' => ['nullable', 'array'],
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
                // UC-008 A4, BR-006: An empty observation is pointed out without losing what was written.
                foreach ($this->array('observations') as $position => $text) {
                    if (is_string($text) && trim($text) === '' && ! $validator->errors()->has("observations.$position")) {
                        $validator->errors()->add("observations.$position", self::EMPTY_OBSERVATION);
                    }
                }

                if ($this->input('decision') === self::OBSERVE
                    && $this->observationTexts() === []
                    && ! $validator->errors()->has('observations')
                    && ! $validator->errors()->has('observations.*')) {
                    $validator->errors()->add('observations', RegisterCommitteePronouncement::OBSERVATION_REQUIRED);
                }
            },
        ];
    }

    /**
     * Whether the member approved the version.
     */
    public function approves(): bool
    {
        return $this->input('decision') === self::APPROVE;
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
            'decision' => 'pronunciamiento',
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
            'decision.required' => 'Elija si aprueba u observa la versión del proyecto.',
            'decision.in' => 'Elija si aprueba u observa la versión del proyecto.',
            'observations.array' => 'Las observaciones no tienen un formato válido.',
            'observations.*.string' => self::EMPTY_OBSERVATION,
            'observations.*.max' => RegisterCommitteePronouncement::OBSERVATION_TOO_LONG,
        ];
    }
}
