<?php

namespace App\Http\Controllers\Accounts;

use App\Actions\Accounts\AccountChangeBlocked;
use App\Actions\Accounts\DeactivateAccount;
use App\Actions\Accounts\FindTeacherAssignments;
use App\Actions\Accounts\ReactivateAccount;
use App\Actions\Accounts\RegisterAccount;
use App\Actions\Accounts\UpdateAccount;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * List the accounts of teachers, the career director and assistants (UC-018 step 2).
     */
    public function index(FindTeacherAssignments $findAssignments): Response
    {
        Gate::authorize('viewAny', User::class);

        $assignable = StoreAccountRequest::assignableRoleValues();

        // UC-018 OQ-053: Administrator accounts are neither listed nor managed here.
        $accounts = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', $assignable))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', RoleName::SystemAdministrator))
            ->with(['roles', 'teacher'])
            ->orderBy('name')
            ->get();

        // UC-018 A2 step 1: Each active account shows what blocks its deactivation before the confirmation.
        $blocking = $findAssignments->handleMany(
            $accounts->where('is_active', true)->pluck('teacher')->filter()->values(),
        );

        $accounts = $accounts->map(fn (User $account): array => [
            'id' => $account->id,
            'name' => $account->name,
            'email' => $account->email,
            'is_active' => $account->is_active,
            'blocking_assignments' => $account->is_active && $account->teacher !== null
                ? $blocking[$account->teacher->id]
                : [],
            'roles' => $account->roles
                ->map(fn (Role $role): string => $role->name->value)
                ->filter(fn (string $role): bool => in_array($role, $assignable, true))
                ->values(),
        ]);

        return Inertia::render('accounts/index', [
            'accounts' => $accounts,
            'assignableRoles' => $assignable,
            'blocked' => session('blocked'),
        ]);
    }

    public function store(StoreAccountRequest $request, RegisterAccount $registerAccount): RedirectResponse
    {
        $registerAccount->handle(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('password'),
            $request->roleNames(),
        );

        return $this->done('Cuenta registrada.');
    }

    public function update(UpdateAccountRequest $request, User $account, UpdateAccount $updateAccount): RedirectResponse
    {
        try {
            $updateAccount->handle(
                $account,
                $request->validated('name'),
                $request->validated('email'),
                $request->roleNames(),
            );
        } catch (AccountChangeBlocked $blocked) {
            return $this->blocked($blocked);
        }

        return $this->done('Cuenta actualizada.');
    }

    public function deactivate(User $account, DeactivateAccount $deactivateAccount): RedirectResponse
    {
        Gate::authorize('deactivate', $account);

        try {
            $deactivateAccount->handle($account);
        } catch (AccountChangeBlocked $blocked) {
            return $this->blocked($blocked);
        }

        return $this->done('Cuenta desactivada.');
    }

    public function reactivate(User $account, ReactivateAccount $reactivateAccount): RedirectResponse
    {
        Gate::authorize('reactivate', $account);

        $reactivateAccount->handle($account);

        return $this->done('Cuenta reactivada.');
    }

    /**
     * UC-018 step 8: Confirm and show the updated list.
     */
    private function done(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('accounts.index');
    }

    /**
     * UC-018 A3 OQ-049: Report what the career director must reassign first.
     */
    private function blocked(AccountChangeBlocked $blocked): RedirectResponse
    {
        return to_route('accounts.index')->with('blocked', [
            'message' => $blocked->getMessage(),
            'assignments' => $blocked->assignments,
        ]);
    }
}
