<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
        \App\Models\Umkm::observe(\App\Observers\UmkmObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Artikel::observe(\App\Observers\ArtikelObserver::class);

        // Register Auth Activity Log Listeners
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                ActivityLogger::log(
                    action: 'LOGIN',
                    description: "User {$event->user->name} ({$event->user->role}) berhasil login ke dashboard.",
                    subjectType: 'Auth',
                    user: $event->user
                );
            }
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user instanceof User) {
                ActivityLogger::log(
                    action: 'LOGOUT',
                    description: "User {$event->user->name} ({$event->user->role}) telah logout dari dashboard.",
                    subjectType: 'Auth',
                    user: $event->user
                );
            }
        });

        if (class_exists(\Laravel\Mcp\Facades\Mcp::class)) {
            \Laravel\Mcp\Facades\Mcp::local('app', \App\Mcp\Servers\AppServer::class);
        }
    }
}
