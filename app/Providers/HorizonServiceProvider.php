<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HorizonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->gate();
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user) {
            return $user !== null && $user->isAlvoraAdmin();
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
