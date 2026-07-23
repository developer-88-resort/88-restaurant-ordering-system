<?php

namespace App\Http\Middleware;

use App\Support\AvailableLocales;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Resolves the active UI language for this request — the "locale"
     * cookie takes priority since it reflects the most recent explicit
     * choice the browser made (e.g. picked on the login page right before
     * signing in, which must carry straight into the dashboard). The
     * authenticated user's saved preference is the fallback for a browser/
     * device that has no cookie yet (e.g. signing in somewhere new), so the
     * choice still follows them across devices and survives logout/login.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale')
            ?? $request->user()?->locale
            ?? config('app.locale');

        if (! in_array($locale, AvailableLocales::CODES, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
