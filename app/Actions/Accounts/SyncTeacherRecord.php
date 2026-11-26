<?php

namespace App\Actions\Accounts;

use App\Enums\RoleName;
use App\Models\Teacher;
use App\Models\User;

/**
 * Keeps the teacher record of an account in line with its teacher role and its state (UC-018 step 7, OQ-038).
 */
class SyncTeacherRecord
{
    public function handle(User $user): void
    {
        $user->load('roles');

        if ($user->hasRole(RoleName::Teacher)) {
            // UC-018 step 7: A teacher account has an active teacher record; the same record is reused (OQ-038).
            Teacher::query()->updateOrCreate(['user_id' => $user->getKey()], ['is_active' => $user->is_active]);

            return;
        }

        // UC-018 OQ-038: Without the teacher role the record becomes inactive and is never deleted.
        Teacher::query()->where('user_id', $user->getKey())->update(['is_active' => false]);
    }
}
