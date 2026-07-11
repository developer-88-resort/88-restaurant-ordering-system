<?php

namespace App\Http\Controllers;

use App\Support\AvailableLocales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Switch the active UI language. Works for both guests (e.g. on the
     * login page) and authenticated users. Persists to a long-lived cookie
     * always, and additionally to the user's saved preference when logged
     * in, so it follows them across devices and survives logout/login.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(AvailableLocales::CODES)],
        ]);

        $locale = $validated['locale'];

        $request->user()?->update(['locale' => $locale]);

        return redirect()->back()->withCookie(
            cookie('locale', $locale, 60 * 24 * 365 * 5)
        );
    }
}
