<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\AvailableLocales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Carry the language chosen on the login page (before this user was
        // known) into their saved account preference, so it also follows
        // them if they next sign in from a browser with no cookie yet.
        $cookieLocale = $request->cookie('locale');
        if ($cookieLocale && in_array($cookieLocale, AvailableLocales::CODES, true)) {
            $request->user()->update(['locale' => $cookieLocale]);
        }

        $redirectTo = match ($request->user()->role) {
            UserRole::Superadmin => route('superadmin.dashboard', absolute: false),
            default => route('profile.edit', absolute: false),
        };

        return redirect()->intended($redirectTo)->with('status', __('Signed in successfully.'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', __('You have been signed out successfully.'));
    }
}
