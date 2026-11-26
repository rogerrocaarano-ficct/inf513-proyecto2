<?php

namespace App\Actions\Accounts;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Keeps at most one active career director (UC-018 BR-002).
 */
class EnsureSingleActiveCareerDirector
{
    /**
     * @param  string  $field  The error key the message is reported under.
     *
     * @throws ValidationException
     */
    public function handle(?User $account, string $field): void
    {
        // UC-018 BR-002: At most one active account has the career director role (A7, OQ-037).
        $director = User::query()
            ->active()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', RoleName::CareerDirector))
            ->when($account, fn (Builder $query, User $account) => $query->whereKeyNot($account->getKey()))
            ->first();

        if ($director !== null) {
            throw ValidationException::withMessages([
                $field => "Ya hay un director de carrera activo: {$director->name}.",
            ]);
        }
    }
}
