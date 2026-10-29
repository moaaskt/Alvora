<?php

namespace App\Listeners;

use App\Events\CourseProgressUpdated;
use App\Models\TrackEnrollment;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class RecalculateTrackEnrollmentProgress implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the event.
     */
    public function handle(CourseProgressUpdated $event): void
    {
        $enrollment = $event->enrollment;

        // Find all track enrollments of this student that contain this course
        $trackEnrollments = TrackEnrollment::where('company_id', $enrollment->company_id)
            ->where('user_id', $enrollment->user_id)
            ->whereHas('track.courses', function ($query) use ($enrollment) {
                $query->where('courses.id', $enrollment->course_id);
            })
            ->with(['track.courses'])
            ->get();

        foreach ($trackEnrollments as $trackEnrollment) {
            $trackEnrollment->updateConsolidatedProgress();
        }
    }
}

