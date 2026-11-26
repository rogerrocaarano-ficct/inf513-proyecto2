<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ChangeInitialPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangeInitialPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class InitialPasswordController extends Controller
{
    /**
     * Show the form to replace the initial password.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->must_change_password) {
            return to_route('dashboard');
        }

        return Inertia::render('auth/change-initial-password', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Replace the initial password and let the user continue.
     */
    public function update(ChangeInitialPasswordRequest $request, ChangeInitialPassword $changeInitialPassword): RedirectResponse
    {
        $changeInitialPassword->handle($request->user(), $request->validated('password'));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Contraseña cambiada.']);

        return to_route('dashboard');
    }
}
