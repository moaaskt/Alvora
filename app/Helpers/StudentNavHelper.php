<?php

namespace App\Helpers;

use App\Models\TrackEnrollment;
use Illuminate\Support\Facades\Auth;

class StudentNavHelper
{
    /**
     * Per-request memoized flag to avoid repeated queries and persistent cache latency.
     */
    protected static ?bool $hasTracks = null;

    /**
     * Check whether current authenticated student has any track enrollments.
     */
    public static function hasTracks(): bool
    {
        if (static::$hasTracks !== null) {
            return static::$hasTracks;
        }

        $userId = Auth::id();
        if (!$userId) {
            return static::$hasTracks = false;
        }

        return static::$hasTracks = TrackEnrollment::where('user_id', $userId)->exists();
    }

    /**
     * Reset memoized state (useful for tests and transitions).
     */
    public static function reset(): void
    {
        static::$hasTracks = null;
    }
}

