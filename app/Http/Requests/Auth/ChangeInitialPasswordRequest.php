<?php

namespace App\Http\Requests\Auth;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangeInitialPasswordRequest extends FormRequest
{
    /**
     * Only a user who still has the initial password changes it here.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->must_change_password;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                // OQ-042: At least 8 characters, confirmed, and different from the initial password.
                Password::defaults(),
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && Hash::check($value, $this->user()->password)) {
                        $fail('La nueva contraseña debe ser distinta de la contraseña inicial.');
                    }
                },
            ],
        ];
    }
}
