<?php

namespace App\Actions\Accounts;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

/**
 * Replaces the roles the administrator assigns, leaving any other role of the account as it is.
 */
class AssignAccountRoles
{
    /**
     * @param  list<RoleName>  $roles
     */
    public function handle(User $account, array $roles): void
    {
        $assignable = array_map(fn (RoleName $role): string => $role->value, RoleName::assignableByAdministrator());
        $chosen = array_map(fn (RoleName $role): string => $role->value, self::withImpliedRoles($roles));

        $keptIds = $account->roles()->whereNotIn('name', $assignable)->pluck('roles.id')->all();
        $chosenIds = Role::query()->whereIn('name', $chosen)->pluck('id')->all();

        $account->roles()->sync([...$keptIds, ...$chosenIds]);
        $account->unsetRelation('roles');
    }

    /**
     * @param  list<RoleName>  $roles
     * @return list<RoleName>
     */
    public static function withImpliedRoles(array $roles): array
    {
        // UC-018 BR-001: Assigning the career director role also assigns the teacher role.
        if (in_array(RoleName::CareerDirector, $roles, true) && ! in_array(RoleName::Teacher, $roles, true)) {
            $roles[] = RoleName::Teacher;
        }

        return array_values(array_unique($roles, SORT_REGULAR));
    }
}
