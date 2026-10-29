<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\TrackEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.student', ['bandHeight' => 340, 'active' => 'dashboard', 'title' => 'Alvora — Meu Aprendizado'])]
class Dashboard extends Component
{
    /**
     * Find next pending lesson or first lesson to continue learning.
     */
    public function continueCourse(int $courseId)
    {
        $user = Auth::user();
        $course = Course::with(['modules.lessons'])->findOrFail($courseId);

        // Fetch all lesson ids ordered by module order and lesson order
        $orderedLessons = $course->modules
            ->sortBy('order')
            ->flatMap(fn ($m) => $m->lessons->sortBy('order'));

        if ($orderedLessons->isEmpty()) {
            session()->flash('warning', 'Este curso ainda não possui aulas cadastradas.');
            return null;
        }

        $completedLessonIds = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $orderedLessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->toArray();

        // First lesson not yet completed
        $nextLesson = $orderedLessons->first(fn ($l) => !in_array($l->id, $completedLessonIds))
            ?? $orderedLessons->first();

        return redirect()->route('student.player', [
            'course' => $course->slug,
            'lesson' => $nextLesson->id,
        ]);
    }

    /**
     * Render the student dashboard.
     */
    public function render(): View
    {
        $user = Auth::user();

        // Active and completed enrollments for student (ordered by updated_at descending)
        $enrollments = Enrollment::with([
                'course' => fn ($q) => $q->withCount([
                    'lessons',
                    'quizzes' => fn ($sq) => $sq->has('questions'),
                ])->withSum('lessons as total_minutes', 'duration_minutes')
                  ->with('modules.lessons')
            ])
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->get();

        $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();

        // Key stats computed over ALL enrollments
        $totalEnrolled = $enrollments->count();
        $completedCount = $enrollments->where('status', 'completed')->count();
        $inProgressCount = $enrollments->where('status', 'active')->where('progress_percent', '<', 100)->count();
        $averageProgress = $totalEnrolled > 0
            ? (int) round($enrollments->avg('progress_percent'))
            : 0;

        // Continuar Aprendendo: at most 3 non-completed courses ordered by updated_at desc
        $inProgressEnrollments = $enrollments
            ->filter(fn ($e) => $e->status !== 'completed' && $e->progress_percent < 100)
            ->take(3);

        // Real number of published courses the student is NOT yet enrolled in
        $availableCoursesCount = Course::where('company_id', $user->company_id)
            ->where('status', 'published')
            ->whereNotIn('id', $enrolledCourseIds)
            ->count();

        // Corporate Learning Tracks for this student (at most 2 on dashboard)
        $trackEnrollments = TrackEnrollment::with(['track.publishedCourses'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        return view('livewire.student.dashboard', [
            'enrollments' => $enrollments,
            'inProgressEnrollments' => $inProgressEnrollments,
            'trackEnrollments' => $trackEnrollments,
            'totalEnrolled' => $totalEnrolled,
            'completedCount' => $completedCount,
            'inProgressCount' => $inProgressCount,
            'averageProgress' => $averageProgress,
            'availableCoursesCount' => $availableCoursesCount,
        ]);
    }
}
