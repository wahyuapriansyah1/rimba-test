<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('view-users', function ($user) {
            return $user && in_array($user->role, ['admin', 'manager']);
        });
        Gate::define('create-user', function ($user) {
            return $user && $user->role === 'admin';
        });
        Gate::define('view-logs', function ($user) {
            return $user && $user->role === 'admin';
        });
    }
}
