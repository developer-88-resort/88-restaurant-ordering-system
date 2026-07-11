<?php

namespace App\Providers;

use App\Events\AuditLogCreated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        // Model changes (Area, MenuCategory, MenuItem, Order, Setting, Space,
        // SpaceCategory, User) are audit-logged via the LogsAuditActivity
        // trait on each model, not an observer — see app/Concerns/LogsAuditActivity.php.

        Event::listen(function (Login $event) {
            activity('audit')
                ->causedBy($event->user)
                ->performedOn($event->user)
                ->event('login')
                ->log("{$event->user->name} logged in.");
        });

        Event::listen(function (Logout $event) {
            if (! $event->user) {
                return;
            }

            activity('audit')
                ->causedBy($event->user)
                ->performedOn($event->user)
                ->event('logout')
                ->log("{$event->user->name} logged out.");
        });

        Event::listen(function (Failed $event) {
            activity('audit')
                ->causedBy($event->user)
                ->event('failed_login')
                ->log('Failed login attempt for: '.($event->credentials['email'] ?? 'unknown'));
        });

        // Keep the Audit Logs page's real-time auto-reload working now that
        // entries are written by spatie/laravel-activitylog instead of the
        // old AuditLog model (which used to broadcast this itself).
        Activity::created(function () {
            broadcast(new AuditLogCreated());
        });
    }
}
