<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\TrackEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.student')]
class Dashboard extends Component
{
    public string $search = '';

    /**
     * Enroll authenticated student in a published course.
     */
    public function enroll(int $courseId): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $course = Course::where('id', $courseId)
            ->where('status', 'published')
            ->firstOrFail();

        Enrollment::firstOrCreate(
            [
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'status' => 'active',
                'progress_percent' => 0,
                'enrolled_at' => now(),
            ]
        );

        session()->flash('success', "Matrícula realizada com sucesso no curso {$course->title}!");
    }

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
        $nextLesson = $orderedLessons->first(fn ($l) => !in_array($l->id, $completedLessonIds));

        // Fallback to first lesson if all completed
        if (!$nextLesson) {
            $nextLesson = $orderedLessons->first();
        }

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

        // Active and completed enrollments for student
        $enrollments = Enrollment::with(['course.modules.lessons'])
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->get();

        $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();

        // Key stats computed over ALL enrollments
        $totalEnrolled = $enrollments->count();
        $completedCount = $enrollments->where('status', 'completed')->count();
        $inProgressCount = $enrollments->where('status', 'active')->count();
        $averageProgress = $totalEnrolled > 0
            ? (int) round($enrollments->avg('progress_percent'))
            : 0;

        // Available courses from same tenant not yet enrolled
        $availableCourses = Course::where('status', 'published')
            ->whereNotIn('id', $enrolledCourseIds)
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->withCount('lessons')
            ->latest()
            ->get();

        // Corporate Learning Tracks for this student (read-only, no write on render)
        $trackEnrollments = TrackEnrollment::with(['track.publishedCourses'])
            ->where('user_id', $user->id)
            ->get();

        return view('livewire.student.dashboard', [
            'enrollments' => $enrollments,
            'trackEnrollments' => $trackEnrollments,
            'totalEnrolled' => $totalEnrolled,
            'completedCount' => $completedCount,
            'inProgressCount' => $inProgressCount,
            'averageProgress' => $averageProgress,
            'availableCourses' => $availableCourses,
        ]);
    }
}
