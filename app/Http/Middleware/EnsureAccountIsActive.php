<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request.
     *
     * Deactivating a user (is_active = false) must take effect immediately,
     * not just block their next login — if their account is deactivated
     * while they still hold a live session, this kicks them out on their
     * very next request instead of letting the old session ride out its
     * timeout on routes that don't already carry a role check.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', __('This account has been deactivated. Contact an administrator for access.'));
        }

        return $next($request);
    }
}
