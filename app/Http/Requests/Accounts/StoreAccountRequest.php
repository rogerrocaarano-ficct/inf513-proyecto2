<?php

namespace App\Http\Requests\Accounts;

use App\Concerns\ProfileValidationRules;
use App\Enums\RoleName;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Data of an account the administrator registers (UC-018 steps 4 to 6).
 */
class StoreAccountRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => [...$this->baseEmailRules(), $this->emailIsFree(null)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(self::assignableRoleValues())],
            // UC-018 BR-005: The initial password has at least 8 characters.
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre completo',
            'email' => 'correo electrónico',
            'roles' => 'roles',
            'roles.*' => 'rol',
            'password' => 'contraseña inicial',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'roles.required' => 'Seleccione al menos un rol.',
            'roles.min' => 'Seleccione al menos un rol.',
        ];
    }

    /**
     * @return list<RoleName>
     */
    public function roleNames(): array
    {
        return array_values(array_map(fn (string $role): RoleName => RoleName::from($role), $this->validated('roles')));
    }

    /**
     * @return list<string>
     */
    public static function assignableRoleValues(): array
    {
        return array_map(fn (RoleName $role): string => $role->value, RoleName::assignableByAdministrator());
    }

    /**
     * @return list<string>
     */
    protected function baseEmailRules(): array
    {
        return ['required', 'string', 'email', 'max:255'];
    }

    /**
     * UC-018 A5: The email must not belong to another user, whatever its letter case.
     */
    protected function emailIsFree(?int $ignoreUserId): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($ignoreUserId): void {
            $taken = User::query()
                ->whereRaw('lower(email) = ?', [mb_strtolower((string) $value)])
                ->when($ignoreUserId, fn ($query, int $id) => $query->whereKeyNot($id))
                ->exists();

            if ($taken) {
                $fail('El correo electrónico ya pertenece a otro usuario.');
            }
        };
    }
}
