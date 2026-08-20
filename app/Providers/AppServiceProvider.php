<?php

namespace App\Providers;

use App\Enums\Permission as PermissionEnum;
use App\Events\OrderCreated;
use App\Listeners\RecordOrderCreatedTrackingEvent;
use App\Models\Role;
use App\Models\User;
use App\Services\SettingsRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Super Admin bypasses every ability.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole(Role::SUPER_ADMIN) ? true : null;
        });

        // Map each permission enum case to a Gate ability of the same name.
        foreach (PermissionEnum::cases() as $permission) {
            Gate::define($permission->value, function (User $user) use ($permission) {
                return $user->hasPermissionTo($permission);
            });
        }

        Event::listen(OrderCreated::class, RecordOrderCreatedTrackingEvent::class);

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('checkout', function (Request $request) {
            $config = config('fortyfive.checkout_rate_limit');

            return Limit::perMinutes(
                max(1, (int) ceil(($config['decay_seconds'] ?? 60) / 60)),
                $config['max_attempts'] ?? 8,
            )->by($request->ip());
        });
    }
}
