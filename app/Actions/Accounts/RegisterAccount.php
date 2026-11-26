<?php

namespace App\Actions\Accounts;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterAccount
{
    public function __construct(
        private readonly AssignAccountRoles $assignRoles,
        private readonly EnsureSingleActiveCareerDirector $ensureSingleDirector,
        private readonly SyncTeacherRecord $syncTeacher,
    ) {}

    /**
     * Create an active account with its roles and its initial password (UC-018 steps 6 and 7).
     *
     * @param  list<RoleName>  $roles
     */
    public function handle(string $name, string $email, string $initialPassword, array $roles): User
    {
        return DB::transaction(function () use ($name, $email, $initialPassword, $roles): User {
            if (in_array(RoleName::CareerDirector, $roles, true)) {
                $this->ensureSingleDirector->handle(null, 'roles');
            }

            // UC-018 BR-005: The user must change the initial password the administrator typed at the first login.
            $account = User::query()->create([
                'name' => $name,
                'email' => mb_strtolower($email),
                'password' => $initialPassword,
                'is_active' => true,
                'must_change_password' => true,
            ]);
            $account->forceFill(['email_verified_at' => now()])->save();

            $this->assignRoles->handle($account, $roles);
            $this->syncTeacher->handle($account);

            return $account;
        });
    }
}
