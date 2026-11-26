<?php

namespace App\Http\Requests\Accounts;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Data of an account the administrator changes (UC-018 A1).
 */
class UpdateAccountRequest extends StoreAccountRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('account'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $account */
        $account = $this->route('account');

        return [
            ...parent::rules(),
            'email' => [...$this->baseEmailRules(), $this->emailIsFree($account->getKey())],
            'password' => ['prohibited'],
        ];
    }
}
