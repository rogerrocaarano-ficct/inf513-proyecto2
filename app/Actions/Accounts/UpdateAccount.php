<?php

namespace App\Actions\Accounts;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateAccount
{
    public function __construct(
        private readonly AssignAccountRoles $assignRoles,
        private readonly EnsureSingleActiveCareerDirector $ensureSingleDirector,
        private readonly FindTeacherAssignments $findAssignments,
        private readonly SyncTeacherRecord $syncTeacher,
    ) {}

    /**
     * Change the name, the email and the roles of an account (UC-018 A1).
     *
     * @param  list<RoleName>  $roles
     *
     * @throws AccountChangeBlocked
     */
    public function handle(User $account, string $name, string $email, array $roles): User
    {
        return DB::transaction(function () use ($account, $name, $email, $roles): User {
            $roles = AssignAccountRoles::withImpliedRoles($roles);

            // UC-018 A7: Newly granting the career director role checks the single active director, any account state.
            if (! $account->hasRole(RoleName::CareerDirector) && in_array(RoleName::CareerDirector, $roles, true)) {
                $this->ensureSingleDirector->handle($account, 'roles');
            }

            // UC-018 BR-003 OQ-049: The whole change is refused, not only the role, and the teacher role is not removed while the teacher holds an assignment.
            if ($account->hasRole(RoleName::Teacher) && ! in_array(RoleName::Teacher, $roles, true)) {
                $assignments = $account->teacher === null ? [] : $this->findAssignments->handle($account->teacher);

                if ($assignments !== []) {
                    throw new AccountChangeBlocked($assignments);
                }
            }

            $account->update(['name' => $name, 'email' => mb_strtolower($email)]);

            $this->assignRoles->handle($account, $roles);
            $this->syncTeacher->handle($account);

            return $account;
        });
    }
}
