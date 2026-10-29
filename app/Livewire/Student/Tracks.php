<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\TrackEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.student', ['bandHeight' => 240, 'active' => 'tracks', 'title' => 'Alvora — Minhas Trilhas Corporativas'])]
class Tracks extends Component
{
    use WithPagination;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $status = 'all'; // 'all' | 'in_progress' | 'completed'

    #[Url(as: 'prioridade')]
    public string $priority = 'all'; // 'all' | 'mandatory' | 'optional'

    #[Url(as: 'ordem')]
    public string $sort = 'recent'; // 'recent' | 'progress' | 'title'

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
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
        $this->priority = 'all';
        $this->sort = 'recent';
        $this->resetPage();
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

        // Check if student has ANY tracks assigned (for edge states)
        $hasAnyTracks = TrackEnrollment::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->exists();

        $query = TrackEnrollment::where('track_enrollments.company_id', $user->company_id)
            ->where('track_enrollments.user_id', $user->id)
            ->with(['track.publishedCourses']);

        // Search filter
        if (!empty($this->search)) {
            $query->whereHas('track', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhere('department', 'like', "%{$this->search}%");
                });
            });
        }

        // Status filter
        if ($this->status === 'in_progress') {
            $query->where(function ($q) {
                $q->where('status', 'active')->where('progress_percent', '<', 100);
            });
        } elseif ($this->status === 'completed') {
            $query->where(function ($q) {
                $q->where('status', 'completed')->orWhere('progress_percent', '>=', 100);
            });
        }

        // Priority filter
        if ($this->priority === 'mandatory') {
            $query->whereHas('track', fn ($q) => $q->where('is_mandatory', true));
        } elseif ($this->priority === 'optional') {
            $query->whereHas('track', fn ($q) => $q->where('is_mandatory', false));
        }

        // Sort order
        if ($this->sort === 'progress') {
            $query->orderByDesc('progress_percent');
        } elseif ($this->sort === 'title') {
            $query->join('learning_tracks', 'track_enrollments.track_id', '=', 'learning_tracks.id')
                  ->orderBy('learning_tracks.title')
                  ->select('track_enrollments.*');
        } else {
            $query->latest('track_enrollments.id');
        }

        $trackEnrollments = $query->paginate(6);

        // Preload student course enrollments to determine course completion in track rows
        $courseEnrollments = Enrollment::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('course_id');

        return view('livewire.student.tracks', [
            'hasAnyTracks' => $hasAnyTracks,
            'trackEnrollments' => $trackEnrollments,
            'courseEnrollments' => $courseEnrollments,
        ]);
    }
}

