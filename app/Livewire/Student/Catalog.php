<?php

namespace App\Livewire\Student;

use App\Actions\Student\EnrollStudentAction;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.student', ['bandHeight' => 240, 'active' => 'catalog', 'title' => 'Alvora — Catálogo de Cursos Corporativos'])]
class Catalog extends Component
{
    use WithPagination;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $status = 'all'; // 'all' | 'available' | 'in_progress' | 'completed'

    #[Url(as: 'ordem')]
    public string $sort = 'recent'; // 'recent' | 'title' | 'duration'

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = 'all';
        $this->sort = 'recent';
        $this->resetPage();
    }

    /**
     * Enroll student in a published course.
     */
    public function enroll(int $courseId, EnrollStudentAction $action): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $action->execute($user, $courseId);
    }

    /**
     * Find next pending lesson or first lesson to continue learning.
     */
    public function continueCourse(int $courseId)
    {
        $user = Auth::user();
        $course = Course::with(['modules.lessons'])->findOrFail($courseId);

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

        $nextLesson = $orderedLessons->first(fn ($l) => !in_array($l->id, $completedLessonIds))
            ?? $orderedLessons->first();

        return redirect()->route('student.player', [
            'course' => $course->slug,
            'lesson' => $nextLesson->id,
        ]);
    }

    public function render(): View
    {
        $user = Auth::user();

        // Total published courses for this tenant (to detect "company has no courses")
        $totalCompanyCourses = Course::where('company_id', $user->company_id)
            ->where('status', 'published')
            ->count();

        // Enrolled count for this user
        $totalUserEnrolled = Enrollment::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->count();

        $query = Course::where('courses.company_id', $user->company_id)
            ->where('courses.status', 'published')
            ->withCount([
                'lessons',
                'quizzes' => fn ($q) => $q->has('questions'),
            ])
            ->withSum('lessons as total_minutes', 'duration_minutes')
            ->with(['enrollments' => fn ($q) => $q->where('user_id', $user->id)]);

        // Search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        // Status filter
        if ($this->status === 'available') {
            $query->whereDoesntHave('enrollments', fn ($q) => $q->where('user_id', $user->id));
        } elseif ($this->status === 'in_progress') {
            $query->whereHas('enrollments', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'active')
                  ->where('progress_percent', '<', 100);
            });
        } elseif ($this->status === 'completed') {
            $query->whereHas('enrollments', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where(function ($sub) {
                      $sub->where('status', 'completed')
                          ->orWhere('progress_percent', '>=', 100);
                  });
            });
        }

        // Sorting
        if ($this->sort === 'title') {
            $query->orderBy('title');
        } elseif ($this->sort === 'duration') {
            $query->orderByDesc('total_minutes');
        } else {
            $query->latest('id');
        }

        $courses = $query->paginate(9);

        return view('livewire.student.catalog', [
            'courses' => $courses,
            'totalCompanyCourses' => $totalCompanyCourses,
            'totalUserEnrolled' => $totalUserEnrolled,
        ]);
    }
}

