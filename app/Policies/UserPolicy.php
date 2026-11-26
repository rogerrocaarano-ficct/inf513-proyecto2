<?php

namespace App\Policies;

use App\Enums\PermissionKey;
use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

/**
 * Who manages which accounts (UC-018, NFR-005).
 */
class UserPolicy
{
    /**
     * Whether the actor holds the account management permission.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(PermissionKey::ManageAccounts);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(PermissionKey::ManageAccounts);
    }

    public function update(User $actor, User $account): bool
    {
        return $this->manages($actor, $account);
    }

    public function deactivate(User $actor, User $account): bool
    {
        return $this->manages($actor, $account) && $account->is_active;
    }

    public function reactivate(User $actor, User $account): bool
    {
        return $this->manages($actor, $account) && ! $account->is_active;
    }

    /**
     * UC-018 step 2 OQ-053: Only accounts of teachers, the career director or assistants are managed here.
     */
    private function manages(User $actor, User $account): bool
    {
        if (! $actor->hasPermission(PermissionKey::ManageAccounts)) {
            return false;
        }

        $assignable = array_map(fn (RoleName $role): string => $role->value, RoleName::assignableByAdministrator());

        return $account->roles->contains(fn (Role $role): bool => in_array($role->name->value, $assignable, true))
            && ! $account->hasRole(RoleName::SystemAdministrator);
    }
}
