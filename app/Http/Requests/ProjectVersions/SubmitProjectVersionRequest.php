<?php

namespace App\Http\Requests\ProjectVersions;

use App\Actions\ProjectVersions\FindVersionAdmission;
use App\Actions\ProjectVersions\VersionAdmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SubmitProjectVersionRequest extends FormRequest
{
    private ?VersionAdmission $admission = null;

    /**
     * Authorization is the `can:versiones.presentar` route middleware (ADR-005).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * What the current phase of the student's process admits.
     */
    public function admission(): VersionAdmission
    {
        return $this->admission ??= app(FindVersionAdmission::class)->handle($this->user());
    }

    /**
     * UC-004 A3: A phase that admits no version is reported before any input error (A4).
     *
     * @throws ValidationException
     */
    protected function prepareForValidation(): void
    {
        if (! $this->admission()->isAdmitted()) {
            throw ValidationException::withMessages(['project_version' => (string) $this->admission()->reason]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            // UC-004 BR-007: Only a project profile takes a title from the student.
            'title' => $this->admission()->requiresTitle() ? ['required', 'string', 'max:300'] : ['nullable'],
            // UC-004 BR-001: The version is a PDF of up to 20 MB (C-004).
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Ingrese el título del proyecto de grado.',
            'title.max' => 'El título del proyecto de grado no debe superar los 300 caracteres.',
            'file.required' => 'Adjunte el archivo de la versión.',
            'file.file' => 'El archivo debe ser PDF.',
            'file.mimes' => 'El archivo debe ser PDF.',
            'file.max' => 'El archivo no debe superar los 20 MB.',
            'file.uploaded' => 'No se pudo subir el archivo. Inténtelo de nuevo.',
        ];
    }
}
