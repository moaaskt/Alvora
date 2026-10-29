<?php

namespace App\Helpers;

class DurationHelper
{
    /**
     * Format minutes into human-readable duration:
     * 0 -> ""
     * 45 -> "45min"
     * 60 -> "1h"
     * 75 -> "1h 15min"
     * 120 -> "2h"
     */
    public static function format(int $minutes): string
    {
        if ($minutes <= 0) {
            return '';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours === 0) {
            return "{$remainingMinutes}min";
        }

        if ($remainingMinutes === 0) {
            return "{$hours}h";
        }

        return "{$hours}h {$remainingMinutes}min";
    }
}

