<?php

namespace App\Providers;

use App\Events\CourseProgressUpdated;
use App\Listeners\RecalculateTrackEnrollmentProgress;
use Illuminate\Support\Facades\Event;
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
        Event::listen(
            CourseProgressUpdated::class,
            RecalculateTrackEnrollmentProgress::class
        );
    }
}
