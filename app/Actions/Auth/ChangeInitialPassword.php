<?php

namespace App\Actions\Auth;

use App\Models\User;

/**
 * Replaces the initial password the administrator or the system gave the user.
 */
class ChangeInitialPassword
{
    public function handle(User $user, string $newPassword): void
    {
        $user->forceFill([
            'password' => $newPassword,
            'must_change_password' => false,
        ])->save();
    }
}
