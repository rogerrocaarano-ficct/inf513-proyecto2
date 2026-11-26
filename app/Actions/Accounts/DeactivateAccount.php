<?php

namespace App\Actions\Accounts;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeactivateAccount
{
    public function __construct(
        private readonly FindTeacherAssignments $findAssignments,
        private readonly SyncTeacherRecord $syncTeacher,
    ) {}

    /**
     * Deactivate an account, which can no longer log in; it is never deleted (UC-018 A2, BR-004).
     *
     * @throws AccountChangeBlocked
     */
    public function handle(User $account): User
    {
        return DB::transaction(function () use ($account): User {
            // UC-018 BR-003: An account is not deactivated while its teacher holds an assignment.
            $assignments = $account->teacher === null ? [] : $this->findAssignments->handle($account->teacher);

            if ($assignments !== []) {
                throw new AccountChangeBlocked($assignments);
            }

            $account->update(['is_active' => false]);
            $this->syncTeacher->handle($account);

            return $account;
        });
    }
}
