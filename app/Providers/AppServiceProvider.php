<?php

namespace App\Providers;

use App\Events\CourseProgressUpdated;
use App\Listeners\RecalculateTrackEnrollmentProgress;
use App\Models\Company;
use App\Models\Course;
use App\Policies\CompanyPolicy;
use App\Policies\CoursePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        config(['blade-icons.components.default' => null]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);

        Event::listen(
            CourseProgressUpdated::class,
            RecalculateTrackEnrollmentProgress::class
        );
    }
}
