<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends a user who still has the initial password to change it before using any other function.
 */
class EnsureInitialPasswordChanged
{
    /**
     * Routes the user can reach while the initial password is pending.
     *
     * @var list<string>
     */
    private const array ALLOWED_ROUTES = ['initial-password.edit', 'initial-password.update', 'logout'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // UC-018 BR-005: The initial password is changed on the first login, before any other function.
        // UC-001 BR-005: The student changes the initial password before any other function.
        if ($request->user()?->must_change_password && ! $request->routeIs(...self::ALLOWED_ROUTES)) {
            return redirect()->route('initial-password.edit');
        }

        return $next($request);
    }
}
