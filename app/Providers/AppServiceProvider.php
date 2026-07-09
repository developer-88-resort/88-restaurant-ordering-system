<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\AuditLog;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Space;
use App\Models\SpaceCategory;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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

        MenuCategory::observe(AuditObserver::class);
        MenuItem::observe(AuditObserver::class);
        Area::observe(AuditObserver::class);
        SpaceCategory::observe(AuditObserver::class);
        Space::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
        Setting::observe(AuditObserver::class);
        Order::observe(AuditObserver::class);

        Event::listen(function (Login $event) {
            AuditLog::create([
                'user_id' => $event->user->id,
                'action' => 'login',
                'auditable_type' => User::class,
                'auditable_id' => $event->user->id,
                'description' => "{$event->user->name} logged in.",
            ]);
        });

        Event::listen(function (Logout $event) {
            if (! $event->user) {
                return;
            }

            AuditLog::create([
                'user_id' => $event->user->id,
                'action' => 'logout',
                'auditable_type' => User::class,
                'auditable_id' => $event->user->id,
                'description' => "{$event->user->name} logged out.",
            ]);
        });

        Event::listen(function (Failed $event) {
            AuditLog::create([
                'user_id' => $event->user?->id,
                'action' => 'failed_login',
                'auditable_type' => User::class,
                'auditable_id' => $event->user?->id,
                'description' => 'Failed login attempt for: '.($event->credentials['email'] ?? 'unknown'),
            ]);
        });
    }
}
