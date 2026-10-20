<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Models\LessonProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LessonPlayer extends Component
{
    public Course $course;
    public ?Lesson $currentLesson = null;
    public ?Enrollment $enrollment = null;
    public bool $isCompleted = false;
    public string $noteContent = '';
    public string $activeTab = 'notes'; // 'notes', 'materials', 'overview'
    public int $progressPercent = 0;
    public array $completedLessonIds = [];

    /**
     * Mount player component with course and optional lesson.
     */
    public function mount(Course $course, ?Lesson $lesson = null): void
    {
        $user = Auth::user();

        // Eager load modules with lessons and attachments
        $this->course = $course->load(['modules.lessons.attachments']);

        // Find or create enrollment for student
        $this->enrollment = Enrollment::firstOrCreate(
            [
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'course_id' => $this->course->id,
            ],
            [
                'status' => 'active',
                'progress_percent' => 0,
                'enrolled_at' => now(),
            ]
        );

        // Fetch all ordered lessons of this course
        $orderedLessons = $this->getOrderedLessons();

        if ($lesson && $lesson->course_id === $this->course->id) {
            $this->currentLesson = $lesson;
        } elseif ($orderedLessons->isNotEmpty()) {
            // Find first uncompleted lesson
            $completedIds = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $orderedLessons->pluck('id'))
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
                ->toArray();

            $pendingLesson = $orderedLessons->first(fn ($l) => !in_array($l->id, $completedIds));
            $this->currentLesson = $pendingLesson ?? $orderedLessons->first();
        }

        $this->loadLessonState();
    }

    /**
     * Get all ordered lessons for the course.
     */
    protected function getOrderedLessons(): Collection
    {
        return $this->course->modules
            ->sortBy('order')
            ->flatMap(fn ($module) => $module->lessons->sortBy('order'))
            ->values();
    }

    /**
     * Load current lesson completion status and student personal note.
     */
    public function loadLessonState(): void
    {
        $user = Auth::user();

        $allLessonIds = $this->course->lessons()->pluck('id');
        $this->completedLessonIds = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $allLessonIds)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->toArray();

        if ($this->currentLesson) {
            $progress = LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $this->currentLesson->id)
                ->first();

            $this->isCompleted = (bool) ($progress && $progress->completed_at);

            $note = LessonNote::where('user_id', $user->id)
                ->where('lesson_id', $this->currentLesson->id)
                ->first();

            $this->noteContent = $note?->content ?? '';
        } else {
            $this->isCompleted = false;
            $this->noteContent = '';
        }

        $this->progressPercent = $this->enrollment->progress_percent;
    }

    /**
     * Switch active lesson dynamically.
     */
    public function selectLesson(int $lessonId): void
    {
        $this->saveNote();

        $lesson = Lesson::where('course_id', $this->course->id)->findOrFail($lessonId);
        $this->currentLesson = $lesson;
        $this->loadLessonState();
    }

    /**
     * Toggle current lesson completed status and recalculate course progress.
     */
    public function toggleComplete(): void
    {
        if (!$this->currentLesson) {
            return;
        }

        $user = Auth::user();

        $progress = LessonProgress::firstOrNew([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'lesson_id' => $this->currentLesson->id,
        ]);

        if ($progress->completed_at) {
            $progress->completed_at = null;
            $this->isCompleted = false;
            $message = 'Aula marcada como pendente.';
        } else {
            $progress->completed_at = now();
            $this->isCompleted = true;
            $message = 'Parabéns! Aula concluída com sucesso.';
        }

        $progress->save();

        $this->progressPercent = $this->enrollment->recalculateProgress();
        $this->loadLessonState();

        session()->flash('success', $message);
    }

    /**
     * Save student personal note with debounce or on demand.
     */
    public function saveNote(): void
    {
        if (!$this->currentLesson) {
            return;
        }

        $user = Auth::user();

        LessonNote::updateOrCreate(
            [
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'lesson_id' => $this->currentLesson->id,
            ],
            [
                'content' => $this->noteContent,
            ]
        );
    }

    /**
     * Navigate to next lesson.
     */
    public function nextLesson(): void
    {
        if (!$this->currentLesson) {
            return;
        }

        $ordered = $this->getOrderedLessons();
        $currentIndex = $ordered->search(fn ($l) => $l->id === $this->currentLesson->id);

        if ($currentIndex !== false && $currentIndex < $ordered->count() - 1) {
            $this->selectLesson($ordered[$currentIndex + 1]->id);
        }
    }

    /**
     * Navigate to previous lesson.
     */
    public function previousLesson(): void
    {
        if (!$this->currentLesson) {
            return;
        }

        $ordered = $this->getOrderedLessons();
        $currentIndex = $ordered->search(fn ($l) => $l->id === $this->currentLesson->id);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->selectLesson($ordered[$currentIndex - 1]->id);
        }
    }

    /**
     * Set active tab below the player.
     */
    public function setTab(string $tab): void
    {
        if (in_array($tab, ['notes', 'materials', 'overview'])) {
            $this->activeTab = $tab;
        }
    }

    /**
     * Render the lesson player view.
     */
    public function render(): View
    {
        $ordered = $this->getOrderedLessons();
        $currentIndex = $this->currentLesson
            ? $ordered->search(fn ($l) => $l->id === $this->currentLesson->id)
            : false;

        $hasPrevious = $currentIndex !== false && $currentIndex > 0;
        $hasNext = $currentIndex !== false && $currentIndex < $ordered->count() - 1;

        return view('livewire.student.lesson-player', [
            'hasPrevious' => $hasPrevious,
            'hasNext' => $hasNext,
        ]);
    }
}
