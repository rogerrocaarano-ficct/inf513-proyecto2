<?php

namespace App\Actions\Accounts;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReactivateAccount
{
    public function __construct(
        private readonly EnsureSingleActiveCareerDirector $ensureSingleDirector,
        private readonly SyncTeacherRecord $syncTeacher,
    ) {}

    /**
     * Reactivate an inactive account (UC-018 A4).
     */
    public function handle(User $account): User
    {
        return DB::transaction(function () use ($account): User {
            // UC-018 BR-002: An account that kept the career director role is not reactivated while another is active.
            if ($account->hasRole(RoleName::CareerDirector)) {
                $this->ensureSingleDirector->handle($account, 'account');
            }

            $account->update(['is_active' => true]);
            $this->syncTeacher->handle($account);

            return $account;
        });
    }
}
